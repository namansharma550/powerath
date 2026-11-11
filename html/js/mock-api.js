/*
 * Browser-only stand-in for the backend so index.html works without a server.
 * DELETE THIS FILE (and its <script> tag) once Laravel serves the endpoints and
 * AXIOM_CONFIG.useMock is false. Endpoint contracts mirror the original Next.js routes:
 *   POST /newsletter   { email, trainingFocus }                       -> { message, promoCode }
 *   GET  /orders       ?email= | ?orderNumber=                        -> { orders: [...] }
 *   POST /orders       { customerName, customerEmail, shippingAddress,
 *                        shippingCity, shippingCountry, promoCode,
 *                        items: [{ productId, option, quantity }] }   -> 201 { order }
 *   POST /reviews      { productId, rating, authorName, athleteRole,
 *                        title, body }                                -> 201 { review }
 * Errors are { error: "message" } with a 4xx status.
 */
(function () {
  var ORDERS_KEY = "axiom_mock_orders_v1";
  var SUBS_KEY = "axiom_mock_subscribers_v1";
  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function load(key) {
    try { return JSON.parse(localStorage.getItem(key)) || []; } catch (e) { return []; }
  }
  function save(key, value) {
    try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { /* ignore */ }
  }
  function clamp(n, lo, hi) { return Math.min(hi, Math.max(lo, n)); }
  function fail(error) { return { ok: false, status: 400, data: { error: error } }; }

  function newsletter(body) {
    var email = String(body.email || "").trim().toLowerCase();
    if (!EMAIL_RE.test(email)) return fail("Enter a valid email address.");
    var subs = load(SUBS_KEY);
    if (subs.indexOf(email) > -1) {
      return { ok: true, status: 200, data: { promoCode: "EARN15", message: "You are already subscribed to the AXIOM Training Briefing. Use code EARN15 at checkout." } };
    }
    subs.push(email);
    save(SUBS_KEY, subs);
    return { ok: true, status: 201, data: { promoCode: "EARN15", message: "Welcome to AXIOM. Code EARN15 unlocked for 15% off your order." } };
  }

  function lookupOrders(query) {
    var email = (query.email || "").trim().toLowerCase();
    var number = (query.orderNumber || "").trim().toUpperCase();
    if (!email && !number) return fail("Provide an email address or order number to look up orders.");
    var found = load(ORDERS_KEY).filter(function (o) {
      return (email && o.customerEmail === email) || (number && o.orderNumber === number);
    });
    return { ok: true, status: 200, data: { orders: found.slice(0, 10) } };
  }

  function createOrder(body) {
    var products = window.AXIOM_DATA.products;
    var name = String(body.customerName || "").trim();
    var email = String(body.customerEmail || "").trim().toLowerCase();
    var address = String(body.shippingAddress || "").trim();
    var city = String(body.shippingCity || "").trim();
    var country = String(body.shippingCountry || "United States").trim();
    var promo = String(body.promoCode || "").trim().toUpperCase();
    var rawItems = Array.isArray(body.items) ? body.items : [];

    if (!name || !email || !address || !city) return fail("Complete all shipping and contact fields to place your order.");
    if (!EMAIL_RE.test(email)) return fail("Enter a valid email address for order confirmation.");
    if (rawItems.length === 0) return fail("Your cart is empty.");

    var items = [];
    var subtotal = 0;
    rawItems.forEach(function (item) {
      var p = products.find(function (x) { return x.id === Number(item.productId); });
      if (!p) return;
      var qty = clamp(Math.round(Number(item.quantity) || 1), 1, 20);
      items.push({
        productId: p.id, sku: p.sku, name: p.name, brand: p.brand,
        option: String(item.option || "").trim() || p.flavorsJson[0] || "Standard",
        quantity: qty, priceCents: p.priceCents, imageUrl: p.imageUrl
      });
      subtotal += p.priceCents * qty;
    });
    if (items.length === 0) return fail("No valid products could be verified.");

    var applied = null, discount = 0;
    if (promo === "EARN15") { applied = "EARN15"; discount = Math.round(subtotal * 0.15); }
    else if (promo === "STACK20" && subtotal >= 12000) { applied = "STACK20"; discount = Math.round(subtotal * 0.2); }

    var net = Math.max(0, subtotal - discount);
    var shipping = net >= 7500 ? 0 : 695;
    var orders = load(ORDERS_KEY);
    var order = {
      id: orders.length + 1,
      orderNumber: "AXM-" + Math.floor(100000 + Math.random() * 900000),
      customerName: name.slice(0, 80), customerEmail: email.slice(0, 120),
      shippingAddress: address.slice(0, 180), shippingCity: city.slice(0, 80), shippingCountry: country.slice(0, 80),
      promoCode: applied, subtotalCents: subtotal, discountCents: discount, shippingCents: shipping,
      totalCents: net + shipping, itemsJson: items,
      status: "CONFIRMED — LOT VERIFIED & QUEUED FOR DISPATCH",
      createdAt: new Date().toISOString()
    };
    orders.unshift(order);
    save(ORDERS_KEY, orders);
    return { ok: true, status: 201, data: { order: order } };
  }

  function createReview(body) {
    var data = window.AXIOM_DATA;
    var author = String(body.authorName || "").trim();
    var title = String(body.title || "").trim();
    var text = String(body.body || "").trim();
    var rating = clamp(Math.round(Number(body.rating) || 5), 1, 5);
    if (author.length < 2) return fail("Please enter your name.");
    if (title.length < 4 || text.length < 12) return fail("Please provide a headline and a detailed review (at least 12 characters).");

    var product = data.products.find(function (p) { return p.id === Number(body.productId); });
    var nextId = data.reviews.reduce(function (m, r) { return Math.max(m, r.id); }, 0) + 1;
    return {
      ok: true, status: 201,
      data: {
        review: {
          id: nextId, productId: product ? product.id : null,
          productName: product ? product.name : "AXIOM Performance Nutrition",
          authorName: author.slice(0, 64),
          athleteRole: String(body.athleteRole || "Verified Training Member").trim().slice(0, 72),
          rating: rating, title: title.slice(0, 120), body: text.slice(0, 800),
          verifiedPurchase: true, createdAt: new Date().toISOString()
        }
      }
    };
  }

  window.AxiomMockApi = {
    handle: function (method, path, body) {
      var parts = path.split("?");
      var query = {};
      (parts[1] || "").split("&").filter(Boolean).forEach(function (pair) {
        var kv = pair.split("=");
        query[decodeURIComponent(kv[0])] = decodeURIComponent(kv[1] || "");
      });
      var route = method + " " + parts[0];
      var result;
      if (route === "POST /newsletter") result = newsletter(body || {});
      else if (route === "GET /orders") result = lookupOrders(query);
      else if (route === "POST /orders") result = createOrder(body || {});
      else if (route === "POST /reviews") result = createReview(body || {});
      else result = { ok: false, status: 404, data: { error: "Not found" } };
      return Promise.resolve(result);
    }
  };
})();
