/*
 * AXIOM storefront behaviour (vanilla JS, no build step).
 * Ported from the Next.js StorefrontClient / CartDrawer / QuickViewModal / Overlays components.
 *
 * Structure:
 *   state  - catalog, filters, cart (persisted in localStorage)
 *   ui     - which modal is open and its transient form state
 *   render - functions that write HTML into the static containers in index.html
 *   events - one delegated click / input / submit listener driven by data-action / data-model / data-form
 */
(function () {
  "use strict";

  var DATA = window.AXIOM_DATA;
  var CONFIG = window.AXIOM_CONFIG || { apiBase: "/api", useMock: false };

  var CART_KEY = "axiom_training_bag_v1";
  var PROMO_KEY = "axiom_promo_code_v1";
  var CATEGORIES = ["ALL", "Whey Protein", "Creatine", "Pre-workout", "Vitamins & Minerals", "Recovery", "Gym Accessories"];
  var SEARCH_TERMS = ["Whey Isolate", "Creapure", "L-Citrulline", "Electrolytes", "Lever Belt", "Vitamin D3"];
  var FREE_SHIPPING_CENTS = 7500;
  var FLAT_SHIPPING_CENTS = 695;

  /* ------------------------------------------------------------------ state */

  var state = {
    products: DATA.products.slice(),
    reviews: DATA.reviews.slice(),
    category: "ALL",
    brand: "ALL",
    sortBy: "featured",
    cart: [],
    promo: "",
    justAddedId: null,
    recentOrders: []
  };

  var ui = {
    modal: null, // 'cart' | 'quick' | 'search' | 'account' | 'article' | 'review' | 'policy'
    cart: { step: "cart", promoInput: "", feedback: "", form: { name: "", email: "", address: "", city: "", country: "United States" }, submitting: false, error: "", order: null },
    quick: { productId: null, option: "", qty: 1, added: false },
    search: { query: "" },
    account: { lookup: "", orders: [], loading: false, searched: false },
    article: { id: null },
    review: { productId: null, rating: 5, authorName: "", athleteRole: "", title: "", body: "", submitting: false, error: "" },
    policy: { key: null }
  };

  /* ---------------------------------------------------------------- helpers */

  function $(selector, root) { return (root || document).querySelector(selector); }

  function esc(value) {
    return String(value == null ? "" : value).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function money(cents) { return "$" + (cents / 100).toFixed(2); }

  function discountPercent(price, compareAt) {
    if (!compareAt || compareAt <= price) return null;
    return Math.round(((compareAt - price) / compareAt) * 100);
  }

  function icon(name, cls, filled) {
    var fill = name === "star" ? ' fill="' + (filled === false ? "none" : "currentColor") + '"' : "";
    return '<svg class="' + (cls || "w-4 h-4") + '"' + fill + ' aria-hidden="true"><use href="#i-' + name + '"/></svg>';
  }

  function stars(rating, cls) {
    var out = "";
    for (var i = 1; i <= 5; i++) out += icon("star", cls, i <= rating);
    return out;
  }

  function findProduct(id) {
    return state.products.find(function (p) { return p.id === Number(id); }) || null;
  }

  function setPath(obj, path, value) {
    var keys = path.split(".");
    var last = keys.pop();
    var target = keys.reduce(function (o, k) { return o[k]; }, obj);
    target[last] = value;
  }

  function apiRequest(method, path, body) {
    if (CONFIG.useMock) return window.AxiomMockApi.handle(method, path, body);
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var headers = { "Accept": "application/json" };
    if (body) headers["Content-Type"] = "application/json";
    if (tokenMeta && tokenMeta.content) headers["X-CSRF-TOKEN"] = tokenMeta.content;
    return fetch(CONFIG.apiBase + path, { method: method, headers: headers, body: body ? JSON.stringify(body) : undefined })
      .then(function (res) {
        return res.json().catch(function () { return {}; }).then(function (data) {
          return { ok: res.ok, status: res.status, data: data };
        });
      });
  }

  /* ------------------------------------------------------------ persistence */

  function loadCart() {
    try {
      var saved = JSON.parse(localStorage.getItem(CART_KEY));
      if (Array.isArray(saved)) state.cart = saved;
      var promo = localStorage.getItem(PROMO_KEY);
      if (promo) state.promo = promo;
    } catch (e) { /* ignore storage errors */ }
  }

  function saveCart() {
    try {
      localStorage.setItem(CART_KEY, JSON.stringify(state.cart));
      localStorage.setItem(PROMO_KEY, state.promo);
    } catch (e) { /* ignore storage errors */ }
  }

  /* ------------------------------------------------------------ cart logic */

  function bagCount() {
    return state.cart.reduce(function (sum, i) { return sum + i.quantity; }, 0);
  }

  function totals() {
    var subtotal = state.cart.reduce(function (sum, i) { return sum + i.priceCents * i.quantity; }, 0);
    var code = state.promo.trim().toUpperCase();
    var discount = 0;
    if (code === "EARN15") discount = Math.round(subtotal * 0.15);
    else if (code === "STACK20" && subtotal >= 12000) discount = Math.round(subtotal * 0.2);

    var net = Math.max(0, subtotal - discount);
    var shipping = state.cart.length === 0 ? 0 : net >= FREE_SHIPPING_CENTS ? 0 : FLAT_SHIPPING_CENTS;
    return {
      code: code,
      subtotal: subtotal,
      discount: discount,
      shipping: shipping,
      total: net + shipping,
      remaining: Math.max(0, FREE_SHIPPING_CENTS - net),
      progress: Math.min(100, Math.round((net / FREE_SHIPPING_CENTS) * 100))
    };
  }

  function addToCart(product, option, quantity) {
    quantity = quantity || 1;
    var chosen = option || product.flavorsJson[0] || "Standard";
    var key = product.id + "::" + chosen;
    var existing = state.cart.find(function (i) { return i.key === key; });

    if (existing) {
      existing.quantity = Math.min(20, existing.quantity + quantity);
    } else {
      state.cart.push({
        key: key, productId: product.id, sku: product.sku, brand: product.brand, name: product.name,
        option: chosen, priceCents: product.priceCents, compareAtPriceCents: product.compareAtPriceCents,
        imageUrl: product.imageUrl, quantity: quantity
      });
    }

    state.justAddedId = product.id;
    setTimeout(function () {
      if (state.justAddedId === product.id) {
        state.justAddedId = null;
        renderProducts();
      }
    }, 1300);

    cartChanged();
    renderProducts();
  }

  function setPromo(code) {
    state.promo = code;
    ui.cart.promoInput = code;
    cartChanged();
  }

  function addPerformanceStack() {
    var whey = state.products.find(function (p) { return p.category === "Whey Protein"; });
    var creatine = state.products.find(function (p) { return p.category === "Creatine"; });
    if (whey) addToCart(whey, whey.flavorsJson[0], 1);
    if (creatine) addToCart(creatine, creatine.flavorsJson[0], 1);
    setPromo("EARN15");
    openModal("cart");
  }

  function updateQty(key, delta) {
    state.cart = state.cart
      .map(function (i) { return i.key === key ? Object.assign({}, i, { quantity: i.quantity + delta }) : i; })
      .filter(function (i) { return i.quantity > 0; });
    cartChanged();
  }

  function removeItem(key) {
    state.cart = state.cart.filter(function (i) { return i.key !== key; });
    cartChanged();
  }

  function cartChanged() {
    saveCart();
    renderChrome();
    if (ui.modal === "cart") renderModal();
  }

  /* -------------------------------------------------------- catalog filtering */

  function visibleProducts() {
    var list = state.products.slice();
    if (state.category !== "ALL") list = list.filter(function (p) { return p.category === state.category; });
    if (state.brand !== "ALL") list = list.filter(function (p) { return p.brand === state.brand; });

    if (state.sortBy === "price-asc") list.sort(function (a, b) { return a.priceCents - b.priceCents; });
    else if (state.sortBy === "price-desc") list.sort(function (a, b) { return b.priceCents - a.priceCents; });
    else if (state.sortBy === "rating") list.sort(function (a, b) { return b.rating - a.rating || b.reviewCount - a.reviewCount; });
    else list.sort(function (a, b) { return a.featuredOrder - b.featuredOrder; });
    return list;
  }

  function scrollToCatalog(category, brand) {
    if (category !== undefined) state.category = category;
    if (brand !== undefined) state.brand = brand;
    setMobileMenu(false);
    renderCatalog();
    var el = document.getElementById("bestsellers");
    if (el) el.scrollIntoView({ behavior: "smooth" });
  }

  /* ----------------------------------------------------------- page renderers */

  function renderChrome() {
    var count = bagCount();
    $("#cart-count").textContent = count;
    $("#cart-btn").setAttribute("aria-label", "Shopping cart with " + count + " items");

    var promoBtn = $("#promo-toggle");
    var active = state.promo === "EARN15";
    promoBtn.textContent = active ? "✓ CODE EARN15 ACTIVE" : "APPLY 15% CODE";
    promoBtn.className = "px-2.5 py-0.5 text-[10px] font-bold tracking-widest border transition-colors cursor-pointer " +
      (active ? "bg-white text-[#101010] border-white" : "bg-[#E3262E] text-white border-[#E3262E] hover:bg-white hover:text-[#101010]");
  }

  function renderCategoryTiles() {
    $("#category-grid").innerHTML = DATA.categoryTiles.map(function (cat, idx) {
      var span = idx < 2 ? "md:col-span-3 lg:col-span-6 min-h-[300px] sm:min-h-[340px]" : "md:col-span-3 lg:col-span-3 min-h-[280px]";
      var selected = state.category === cat.id;
      return '<button type="button" data-action="shop" data-category="' + esc(cat.id) + '" class="' + span +
        ' group relative bg-[#101010] text-white border ' + (selected ? "border-[#E3262E] ring-2 ring-[#E3262E]" : "border-[#101010]") +
        ' overflow-hidden text-left flex flex-col justify-between p-6 cursor-pointer">' +
        '<img src="' + esc(cat.imageUrl) + '" alt="' + esc(cat.title) + '" loading="lazy" class="absolute inset-0 w-full h-full object-cover opacity-55 group-hover:opacity-70 group-hover:scale-105 transition-all duration-500">' +
        '<div class="absolute inset-0 bg-black/45 group-hover:bg-black/35 transition-colors"></div>' +
        '<div class="relative z-10 flex items-center justify-between w-full">' +
          '<span class="text-[11px] font-mono-spec uppercase tracking-widest bg-[#101010]/90 border border-white/20 px-2.5 py-1">' + esc(cat.code) + '</span>' +
          '<span class="text-[10px] font-mono-spec font-semibold uppercase tracking-widest bg-[#E3262E] text-white px-2.5 py-1">' + esc(cat.metric) + '</span>' +
        '</div>' +
        '<div class="relative z-10 mt-auto pt-12"><div class="flex items-end justify-between gap-3"><div>' +
          '<h3 class="font-display text-3xl sm:text-4xl font-extrabold uppercase leading-none group-hover:text-[#E3262E] transition-colors">' + esc(cat.title) + '</h3>' +
          '<p class="text-xs text-neutral-200 mt-2 max-w-sm leading-relaxed">' + esc(cat.tagline) + '</p>' +
        '</div><span class="w-10 h-10 bg-white text-[#101010] group-hover:bg-[#E3262E] group-hover:text-white transition-colors flex items-center justify-center shrink-0">' + icon("arrow-up-right") + '</span></div></div>' +
        '</button>';
    }).join("");
  }

  function renderTabs() {
    $("#category-tabs").innerHTML = CATEGORIES.map(function (cat) {
      var active = state.category === cat;
      var label = cat === "ALL" ? "All Products (" + state.products.length + ")" : cat;
      return '<button type="button" role="tab" aria-selected="' + active + '" data-action="set-category" data-category="' + esc(cat) +
        '" class="px-4 py-2 text-xs font-mono-spec font-semibold uppercase tracking-wider whitespace-nowrap border transition-colors cursor-pointer ' +
        (active ? "bg-[#101010] text-white border-[#101010]" : "bg-[#F4F3F0] text-[#101010] border-[#DCDAD4] hover:border-[#101010]") + '">' + esc(label) + '</button>';
    }).join("");
  }

  function renderBrandChip() {
    $("#brand-chip").innerHTML = state.brand === "ALL" ? "" :
      '<button type="button" data-action="clear-brand" class="px-3 py-2 bg-[#101010] text-white text-xs font-mono-spec uppercase flex items-center gap-2 cursor-pointer">' +
      "<span>BRAND: " + esc(state.brand) + "</span>" + icon("close", "w-3.5 h-3.5 text-[#E3262E]") + "</button>";
  }

  function productCard(p) {
    var discount = discountPercent(p.priceCents, p.compareAtPriceCents);
    var added = state.justAddedId === p.id;
    return '<article class="group bg-[#F4F3F0] border border-[#DCDAD4] hover:border-[#101010] transition-colors flex flex-col justify-between">' +
      "<div>" +
        '<div class="relative aspect-[4/3] bg-[#101010] overflow-hidden border-b border-[#DCDAD4]">' +
          '<img src="' + esc(p.imageUrl) + '" alt="' + esc(p.brand + " " + p.name) + '" loading="lazy" data-action="quick-view" data-id="' + p.id + '" class="w-full h-full object-cover group-hover:scale-104 transition-transform duration-500 cursor-pointer">' +
          '<div class="absolute top-3 left-3 flex flex-col gap-1.5">' +
            (discount ? '<span class="bg-[#E3262E] text-white text-[10px] font-mono-spec font-bold uppercase tracking-widest px-2.5 py-1">SAVE ' + discount + "%</span>" : "") +
            (p.badge ? '<span class="bg-[#101010]/90 text-white border border-white/20 text-[10px] font-mono-spec uppercase tracking-widest px-2.5 py-1">' + esc(p.badge) + "</span>" : "") +
          "</div>" +
          '<button type="button" data-action="quick-view" data-id="' + p.id + '" class="absolute bottom-3 right-3 bg-white/95 text-[#101010] hover:bg-[#E3262E] hover:text-white border border-[#101010] px-3 py-1.5 text-[11px] font-mono-spec font-bold uppercase tracking-wider transition-colors cursor-pointer">QUICK VIEW + FACTS</button>' +
        "</div>" +
        '<div class="p-5">' +
          '<div class="flex items-center justify-between gap-2 text-[11px] font-mono-spec">' +
            '<button type="button" data-action="set-brand" data-brand="' + esc(p.brand) + '" class="font-semibold uppercase tracking-widest text-[#E3262E] hover:underline cursor-pointer">' + esc(p.brand) + "</button>" +
            '<div class="flex items-center gap-1 text-[#101010]" aria-label="Rated ' + p.rating + " out of 5 from " + p.reviewCount + ' reviews">' +
              icon("star", "w-3.5 h-3.5 text-[#E3262E]") + '<span class="font-bold">' + p.rating.toFixed(1) + '</span><span class="text-neutral-500">(' + p.reviewCount + ")</span>" +
            "</div>" +
          "</div>" +
          '<h3 class="mt-2 font-display text-2xl font-extrabold uppercase leading-[1.02] text-[#101010]"><button type="button" data-action="quick-view" data-id="' + p.id + '" class="text-left hover:text-[#E3262E] transition-colors cursor-pointer">' + esc(p.name) + "</button></h3>" +
          '<p class="mt-1.5 text-xs text-neutral-600 line-clamp-2 min-h-[2rem]">' + esc(p.subtitle) + "</p>" +
          '<div class="mt-3 pt-3 border-t border-[#DCDAD4] flex items-baseline justify-between">' +
            '<div class="flex items-baseline gap-2"><span class="font-display text-3xl font-extrabold text-[#101010]">' + money(p.priceCents) + "</span>" +
              (p.compareAtPriceCents ? '<span class="text-xs font-mono-spec text-neutral-400 line-through">' + money(p.compareAtPriceCents) + "</span>" : "") + "</div>" +
            '<span class="text-[10px] font-mono-spec uppercase text-neutral-500">' + esc(p.flavorsJson[0]) + "</span>" +
          "</div>" +
        "</div>" +
      "</div>" +
      '<div class="px-5 pb-5 grid grid-cols-12 gap-2">' +
        '<button type="button" data-action="add-to-cart" data-id="' + p.id + '" class="col-span-8 h-11 font-display text-base font-bold uppercase tracking-wider transition-colors flex items-center justify-center gap-1.5 cursor-pointer ' +
          (added ? "bg-[#101010] text-white" : "bg-[#E3262E] text-white hover:bg-[#101010]") + '">' +
          (added ? icon("check") + "<span>ADDED TO BAG</span>" : "<span>ADD TO CART</span>") + "</button>" +
        '<button type="button" data-action="quick-view" data-id="' + p.id + '" class="col-span-4 h-11 border border-[#101010] bg-white text-[#101010] hover:bg-[#101010] hover:text-white font-display text-sm font-bold uppercase tracking-wider transition-colors cursor-pointer">QUICK VIEW</button>' +
      "</div>" +
    "</article>";
  }

  function renderProducts() {
    var list = visibleProducts();
    $("#product-grid").innerHTML = list.length === 0 ?
      '<div class="py-16 text-center bg-[#F4F3F0] border border-[#DCDAD4] mt-8">' +
        '<p class="font-display text-2xl font-bold uppercase">NO FORMULATIONS MATCH YOUR ACTIVE FILTER</p>' +
        '<button type="button" data-action="reset-filters" class="mt-4 px-6 py-2.5 bg-[#101010] text-white text-xs font-mono-spec uppercase tracking-wider hover:bg-[#E3262E] cursor-pointer">RESET FILTERS</button>' +
      "</div>" :
      '<div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">' + list.map(productCard).join("") + "</div>";
  }

  function renderBrandStrip() {
    $("#brand-strip").innerHTML = DATA.brands.map(function (b) {
      var selected = state.brand === b.name;
      return '<button type="button" data-action="shop" data-category="ALL" data-brand="' + (selected ? "ALL" : esc(b.name)) +
        '" class="p-5 text-left transition-colors cursor-pointer ' + (selected ? "bg-[#101010] text-white" : "bg-[#F4F3F0] hover:bg-[#101010] hover:text-white") + '">' +
        '<p class="font-display text-xl font-extrabold uppercase tracking-tight leading-none">' + esc(b.name) + "</p>" +
        '<p class="text-[10px] font-mono-spec uppercase tracking-wider text-[#E3262E] mt-1.5">' + esc(b.tag) + "</p></button>";
    }).join("");
  }

  function renderCatalog() {
    renderCategoryTiles();
    renderTabs();
    renderBrandChip();
    renderBrandStrip();
    renderProducts();
    $("#sort-select").value = state.sortBy;
  }

  function renderGuides() {
    var lead = DATA.articles[0];
    var rest = DATA.articles.slice(1);
    $("#guides-grid").innerHTML =
      '<article class="lg:col-span-7 bg-white border border-[#101010] flex flex-col justify-between group"><div>' +
        '<div class="aspect-[16/9] bg-[#101010] overflow-hidden border-b border-[#101010] relative">' +
          '<img src="' + esc(lead.imageUrl) + '" alt="' + esc(lead.title) + '" loading="lazy" class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500">' +
          '<span class="absolute top-4 left-4 bg-[#E3262E] text-white px-3 py-1 text-[10px] font-mono-spec font-bold uppercase tracking-widest">' + esc(lead.category) + "</span></div>" +
        '<div class="p-6 sm:p-8"><span class="text-[11px] font-mono-spec uppercase tracking-widest text-neutral-500">' + esc(lead.readTime) + " // CLINICAL BRIEFING</span>" +
          '<h3 class="font-display text-3xl sm:text-4xl font-extrabold uppercase leading-[0.98] mt-2">' + esc(lead.title) + "</h3>" +
          '<p class="text-sm text-neutral-600 leading-relaxed mt-3">' + esc(lead.excerpt) + "</p></div></div>" +
        '<div class="px-6 sm:px-8 pb-6 sm:pb-8"><button type="button" data-action="open-article" data-id="' + esc(lead.id) + '" class="px-6 py-3 bg-[#101010] text-white font-display text-base font-bold uppercase tracking-wider hover:bg-[#E3262E] transition-colors inline-flex items-center gap-2 cursor-pointer"><span>READ FULL PROTOCOL</span>' + icon("arrow-up-right") + "</button></div>" +
      "</article>" +
      '<div class="lg:col-span-5 flex flex-col gap-4">' + rest.map(function (art) {
        return '<article data-action="open-article" data-id="' + esc(art.id) + '" class="bg-white border border-[#DCDAD4] hover:border-[#101010] p-5 flex gap-4 items-start transition-colors cursor-pointer group">' +
          '<img src="' + esc(art.imageUrl) + '" alt="' + esc(art.title) + '" loading="lazy" class="w-24 h-24 sm:w-28 sm:h-28 object-cover bg-[#101010] shrink-0 border border-[#DCDAD4]">' +
          '<div class="flex-1 min-w-0"><div class="flex items-center gap-2 text-[10px] font-mono-spec uppercase tracking-widest"><span class="text-[#E3262E] font-bold">' + esc(art.category) + '</span><span class="text-neutral-400">· ' + esc(art.readTime) + "</span></div>" +
            '<h3 class="font-display text-xl sm:text-2xl font-extrabold uppercase leading-tight mt-1 group-hover:text-[#E3262E] transition-colors">' + esc(art.title) + "</h3>" +
            '<p class="text-xs text-neutral-600 line-clamp-2 mt-1.5">' + esc(art.excerpt) + "</p>" +
            '<span class="inline-block mt-2 text-[11px] font-mono-spec font-bold uppercase underline">Open Guide →</span></div></article>';
      }).join("") + "</div>";
  }

  function renderReviews() {
    $("#reviews-grid").innerHTML = state.reviews.slice(0, 4).map(function (r) {
      return '<article class="bg-[#F4F3F0] border border-[#DCDAD4] p-6 flex flex-col justify-between"><div>' +
        '<div class="flex items-center justify-between mb-3"><div class="flex text-[#E3262E]">' + stars(r.rating, "w-3.5 h-3.5") + "</div>" +
          (r.verifiedPurchase ? '<span class="text-[10px] font-mono-spec uppercase tracking-wider bg-white border border-[#DCDAD4] px-2 py-0.5 font-semibold">✓ VERIFIED BUYER</span>' : "") + "</div>" +
        '<h3 class="font-display text-xl font-extrabold uppercase leading-snug text-[#101010]">&ldquo;' + esc(r.title) + "&rdquo;</h3>" +
        '<p class="text-xs text-neutral-700 leading-relaxed mt-2.5">' + esc(r.body) + "</p></div>" +
        '<div class="mt-6 pt-4 border-t border-[#DCDAD4]"><p class="font-bold text-xs uppercase text-[#101010]">' + esc(r.authorName) + "</p>" +
          '<p class="text-[11px] font-mono-spec text-neutral-500">' + esc(r.athleteRole) + "</p>" +
          '<p class="text-[10px] font-mono-spec text-[#E3262E] uppercase mt-1.5 truncate">ITEM: ' + esc(r.productName) + "</p></div></article>";
    }).join("");
  }

  function renderFooterCategories() {
    $("#footer-categories").innerHTML = DATA.categoryTiles.map(function (c) {
      return '<li><button type="button" data-action="shop" data-category="' + esc(c.id) + '" class="text-neutral-400 hover:text-white transition-colors cursor-pointer">' + esc(c.title) + "</button></li>";
    }).join("");
  }

  function renderStructuredData() {
    var data = {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          name: "AXIOM Sports Nutrition",
          description: "Clinical sports nutrition, ISO/IEC 17025 batch-tested whey protein isolates, Creapure creatine monohydrate, and competition-spec strength equipment.",
          telephone: "+1-800-555-0194",
          address: { "@type": "PostalAddress", streetAddress: "420 Industrial Foundry Way, Suite 200", addressLocality: "Austin", addressRegion: "TX", postalCode: "78702", addressCountry: "US" }
        },
        {
          "@type": "ItemList",
          name: "AXIOM Bestselling Sports Nutrition & Fitness Equipment",
          itemListElement: state.products.map(function (p, i) {
            return {
              "@type": "ListItem", position: i + 1,
              item: {
                "@type": "Product", name: p.name, sku: p.sku, description: p.description, image: p.imageUrl, category: p.category,
                brand: { "@type": "Brand", name: p.brand },
                offers: { "@type": "Offer", priceCurrency: "USD", price: (p.priceCents / 100).toFixed(2), availability: p.inStock ? "https://schema.org/InStock" : "https://schema.org/OutOfStock" },
                aggregateRating: { "@type": "AggregateRating", ratingValue: p.rating.toFixed(1), reviewCount: p.reviewCount }
              }
            };
          })
        }
      ]
    };
    var el = document.createElement("script");
    el.type = "application/ld+json";
    el.textContent = JSON.stringify(data);
    document.head.appendChild(el);
  }

  function setMobileMenu(open) {
    var nav = $("#mobile-navigation");
    var btn = $("#mobile-menu-btn");
    nav.classList.toggle("hidden", !open);
    btn.setAttribute("aria-expanded", String(open));
    $(".menu-open-icon", btn).classList.toggle("hidden", open);
    $(".menu-close-icon", btn).classList.toggle("hidden", !open);
  }

  /* ----------------------------------------------------------------- modals */

  var MODAL_ID = { cart: "cart-drawer-heading", quick: "quickview-product-title", search: "", account: "account-modal-title", article: "article-modal-title", review: "write-review-title", policy: "policy-modal-title" };

  function openModal(type) {
    ui.modal = type;
    setMobileMenu(false);
    renderModal();
    var focusTarget = $("#modal-root [data-autofocus]");
    if (focusTarget) focusTarget.focus();
  }

  function closeModal() {
    if (ui.modal === "cart" && ui.cart.step === "confirmed") ui.cart.step = "cart";
    ui.modal = null;
    renderModal();
  }

  function renderModal() {
    var root = $("#modal-root");
    document.body.style.overflow = ui.modal ? "hidden" : "";
    if (!ui.modal) { root.innerHTML = ""; return; }

    // preserve scroll position of the scrollable body across re-renders
    var scroller = $("#modal-root [data-scroll]");
    var scrollTop = scroller ? scroller.scrollTop : 0;

    var html = "";
    if (ui.modal === "cart") html = cartHtml();
    else if (ui.modal === "quick") html = quickViewHtml();
    else if (ui.modal === "search") html = searchHtml();
    else if (ui.modal === "account") html = accountHtml();
    else if (ui.modal === "article") html = articleHtml();
    else if (ui.modal === "review") html = reviewHtml();
    else if (ui.modal === "policy") html = policyHtml();
    root.innerHTML = html;

    var next = $("#modal-root [data-scroll]");
    if (next && scroller) next.scrollTop = scrollTop;
  }

  function backdrop(inner, classes, label) {
    return '<div class="fixed inset-0 z-50 ' + classes + '" role="dialog" aria-modal="true" ' +
      (label ? 'aria-labelledby="' + label + '"' : 'aria-label="Search catalog"') + ' data-action="backdrop">' + inner + "</div>";
  }

  /* ---- cart drawer */

  function cartHtml() {
    var c = ui.cart;
    var t = totals();
    var items = state.cart;
    var heading = c.step === "cart" ? "Training Bag (" + bagCount() + ")" : c.step === "checkout" ? "Express Dispatch Checkout" : "Order Confirmed";

    var meter = "";
    if (c.step !== "confirmed") {
      meter = '<div class="bg-[#F4F3F0] px-6 py-3 border-b border-[#DCDAD4]">' +
        '<div class="flex items-center justify-between text-[11px] font-mono-spec font-semibold uppercase tracking-wider mb-1.5">' +
          (t.remaining === 0 && items.length > 0 ? '<span class="text-[#101010]">✓ COMPLIMENTARY 2-DAY EXPRESS SHIPPING UNLOCKED</span>' :
            '<span>ADD <strong class="text-[#E3262E]">' + money(t.remaining) + "</strong> FOR FREE EXPRESS SHIPPING</span>") +
          "<span>" + t.progress + "%</span></div>" +
        '<div class="w-full h-1.5 bg-[#DCDAD4] overflow-hidden"><div class="h-full bg-[#E3262E] transition-all duration-300" style="width:' + (items.length === 0 ? 0 : t.progress) + '%"></div></div></div>';
    }

    var body;
    if (c.step === "confirmed" && c.order) body = cartConfirmedHtml(c.order);
    else if (c.step === "checkout") body = cartCheckoutHtml();
    else if (items.length === 0) body = cartEmptyHtml();
    else body = cartItemsHtml(t);

    var footer = "";
    if (items.length > 0 && c.step !== "confirmed") {
      footer = '<div class="border-t border-[#101010] bg-[#F4F3F0] px-6 py-5 space-y-3"><div class="space-y-1.5 text-xs">' +
        '<div class="flex justify-between text-neutral-600"><span>Merchandise Subtotal</span><span class="font-mono-spec font-semibold text-[#101010]">' + money(t.subtotal) + "</span></div>" +
        (t.discount > 0 ? '<div class="flex justify-between text-[#E3262E] font-semibold"><span>Promo Discount (' + esc(t.code) + ')</span><span class="font-mono-spec">−' + money(t.discount) + "</span></div>" : "") +
        '<div class="flex justify-between text-neutral-600"><span>Express Tracked Shipping</span><span class="font-mono-spec font-semibold text-[#101010]">' + (t.shipping === 0 ? "FREE" : money(t.shipping)) + "</span></div>" +
        '<div class="flex justify-between text-base font-bold pt-2 border-t border-[#DCDAD4]"><span class="font-display uppercase text-xl">ESTIMATED TOTAL</span><span class="font-mono-spec text-lg">' + money(t.total) + "</span></div></div>" +
        (c.step === "cart" ?
          '<button type="button" data-action="cart-checkout" class="w-full h-13 bg-[#E3262E] text-white font-display text-xl font-bold uppercase tracking-wider hover:bg-[#101010] transition-colors cursor-pointer flex items-center justify-center gap-2">' + icon("lock") + "<span>PROCEED TO SECURE CHECKOUT</span></button>" :
          '<button type="submit" form="express-checkout-form"' + (c.submitting ? " disabled" : "") + ' class="w-full h-13 bg-[#E3262E] text-white font-display text-xl font-bold uppercase tracking-wider hover:bg-[#101010] transition-colors cursor-pointer">' +
            (c.submitting ? "VERIFYING LOT &amp; CONFIRMING..." : "COMPLETE ORDER — " + money(t.total)) + "</button>") +
        "</div>";
    }

    var inner = '<aside class="w-full max-w-md sm:max-w-lg bg-white text-[#101010] h-full flex flex-col border-l border-[#101010] shadow-2xl" data-stop>' +
      '<div class="flex items-center justify-between px-6 py-4 bg-[#101010] text-white border-b border-[#252525]"><div class="flex items-center gap-2.5">' +
        icon("bag", "w-5 h-5 text-[#E3262E]") + '<h2 id="cart-drawer-heading" class="font-display text-2xl font-extrabold uppercase tracking-wide">' + esc(heading) + "</h2></div>" +
        '<button type="button" data-action="close-modal" aria-label="Close shopping cart" class="text-neutral-400 hover:text-white transition-colors p-1 cursor-pointer">' + icon("close", "w-5 h-5") + "</button></div>" +
      meter +
      '<div class="flex-1 overflow-y-auto px-6 py-5" data-scroll>' + body + "</div>" + footer + "</aside>";
    return backdrop(inner, "flex justify-end bg-black/75", MODAL_ID.cart);
  }

  function cartEmptyHtml() {
    return '<div class="h-full flex flex-col items-center justify-center text-center py-14">' +
      '<div class="w-14 h-14 bg-[#F4F3F0] border border-[#DCDAD4] flex items-center justify-center mb-4">' + icon("bag", "w-6 h-6 text-neutral-500") + "</div>" +
      '<h3 class="font-display text-2xl font-extrabold uppercase">YOUR TRAINING BAG IS EMPTY</h3>' +
      '<p class="text-xs text-neutral-500 max-w-xs mt-1.5 leading-relaxed">Explore third-party tested whey isolates, Creapure® monohydrate, and competition-grade strength gear.</p>' +
      '<button type="button" data-action="close-modal" class="mt-6 px-7 py-3 bg-[#101010] text-white font-display text-base font-bold uppercase tracking-wider hover:bg-[#E3262E] transition-colors cursor-pointer">EXPLORE BESTSELLERS</button></div>';
  }

  function cartItemsHtml(t) {
    var c = ui.cart;
    var rows = state.cart.map(function (item) {
      return '<div class="flex gap-4 pb-4 border-b border-[#DCDAD4]">' +
        '<img src="' + esc(item.imageUrl) + '" alt="' + esc(item.name) + '" class="w-20 h-20 object-cover bg-[#101010] border border-[#DCDAD4] shrink-0">' +
        '<div class="flex-1 min-w-0"><div class="flex items-start justify-between gap-2"><div>' +
          '<p class="text-[10px] font-mono-spec uppercase tracking-widest text-[#E3262E] font-semibold">' + esc(item.brand) + "</p>" +
          '<h4 class="font-display text-lg font-bold uppercase leading-tight text-[#101010] line-clamp-1">' + esc(item.name) + "</h4>" +
          '<p class="text-[11px] text-neutral-500 mt-0.5">' + esc(item.option) + "</p></div>" +
          '<button type="button" data-action="cart-remove" data-key="' + esc(item.key) + '" aria-label="Remove ' + esc(item.name) + '" class="text-neutral-400 hover:text-[#E3262E] p-1 transition-colors cursor-pointer">' + icon("trash") + "</button></div>" +
        '<div class="flex items-center justify-between mt-3"><div class="flex items-center border border-[#DCDAD4] bg-[#F4F3F0]">' +
          '<button type="button" data-action="cart-qty" data-key="' + esc(item.key) + '" data-delta="-1" aria-label="Decrease item quantity" class="w-7 h-7 flex items-center justify-center text-sm font-bold hover:bg-[#101010] hover:text-white transition-colors cursor-pointer">−</button>' +
          '<span class="w-8 text-center font-mono-spec text-xs font-bold">' + item.quantity + "</span>" +
          '<button type="button" data-action="cart-qty" data-key="' + esc(item.key) + '" data-delta="1" aria-label="Increase item quantity" class="w-7 h-7 flex items-center justify-center text-sm font-bold hover:bg-[#101010] hover:text-white transition-colors cursor-pointer">+</button></div>' +
          '<div class="text-right"><span class="font-mono-spec text-sm font-bold text-[#101010]">' + money(item.priceCents * item.quantity) + "</span></div></div></div></div>";
    }).join("");

    return '<div class="space-y-4">' + rows +
      '<form data-form="promo" class="pt-2"><label for="promo-input" class="block text-[10px] font-mono-spec font-semibold uppercase tracking-widest text-neutral-500 mb-1.5">PROMOTIONAL CODE</label>' +
        '<div class="flex gap-2"><input id="promo-input" type="text" data-model="cart.promoInput" value="' + esc(c.promoInput) + '" placeholder="Enter code (e.g. EARN15)" class="flex-1 h-10 px-3 bg-[#F4F3F0] border border-[#DCDAD4] text-xs font-mono-spec uppercase">' +
        '<button type="submit" class="px-4 h-10 bg-[#101010] text-white text-xs font-bold uppercase tracking-wider hover:bg-[#E3262E] transition-colors cursor-pointer">APPLY</button></div>' +
        (!t.code ? '<button type="button" data-action="cart-apply-earn15" class="mt-2 text-[11px] font-mono-spec text-[#E3262E] hover:underline cursor-pointer">+ Tap to apply active cycle code: EARN15 (15% OFF)</button>' : "") +
        (c.feedback ? '<p class="mt-1.5 text-[11px] font-medium text-[#101010] flex items-center gap-1">' + icon("check", "w-3.5 h-3.5 text-[#E3262E]") + "<span>" + esc(c.feedback) + "</span></p>" : "") +
      "</form></div>";
  }

  function field(label, name, value, attrs) {
    return '<div><label class="block text-[11px] font-mono-spec font-semibold uppercase tracking-wider mb-1">' + label + "</label>" +
      '<input type="' + (attrs.type || "text") + '" required data-model="cart.form.' + name + '" value="' + esc(value) + '" placeholder="' + esc(attrs.placeholder) + '" class="w-full h-11 px-3.5 bg-[#F4F3F0] border border-[#DCDAD4] focus:border-[#101010] text-sm"></div>';
  }

  function cartCheckoutHtml() {
    var c = ui.cart;
    var countries = ["United States", "Canada", "United Kingdom", "Australia", "Germany"].map(function (name) {
      return '<option value="' + name + '"' + (c.form.country === name ? " selected" : "") + ">" + name + "</option>";
    }).join("");
    return '<form id="express-checkout-form" data-form="checkout" class="space-y-4">' +
      '<div class="flex items-center justify-between pb-3 border-b border-[#DCDAD4]"><span class="text-xs font-mono-spec uppercase tracking-widest text-neutral-500">SHIPPING &amp; RECIPIENT DETAILS</span>' +
        '<button type="button" data-action="cart-back" class="text-xs font-semibold uppercase underline hover:text-[#E3262E] cursor-pointer">← Edit Bag</button></div>' +
      (c.error ? '<div class="bg-[#E3262E]/10 border border-[#E3262E] text-[#E3262E] px-3.5 py-2.5 text-xs font-semibold" role="alert">' + esc(c.error) + "</div>" : "") +
      field("Full Name *", "name", c.form.name, { placeholder: "Alex Mercer" }) +
      field("Email Address (for Order Tracking) *", "email", c.form.email, { type: "email", placeholder: "athlete@domain.com" }) +
      field("Street Address *", "address", c.form.address, { placeholder: "740 Ironworks Blvd, Suite 4B" }) +
      '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">' +
        field("City, State &amp; Postal *", "city", c.form.city, { placeholder: "Austin, TX 78701" }) +
        '<div><label class="block text-[11px] font-mono-spec font-semibold uppercase tracking-wider mb-1">Country *</label>' +
          '<select data-model="cart.form.country" class="w-full h-11 px-3 bg-[#F4F3F0] border border-[#DCDAD4] focus:border-[#101010] text-sm">' + countries + "</select></div></div>" +
      '<div class="bg-[#F4F3F0] border border-[#DCDAD4] p-3.5 text-xs space-y-1.5 mt-4"><div class="flex items-center gap-2 font-semibold text-[#101010]">' + icon("lock", "w-4 h-4 text-[#E3262E]") + "<span>256-Bit Encrypted Direct Fulfillment</span></div>" +
        '<p class="text-neutral-600 text-[11px] leading-relaxed">All supplements ship factory-sealed with lot traceability numbers. By placing this order you confirm you are 18+ purchasing lawful sports nutrition &amp; athletic gear.</p></div></form>';
  }

  function cartConfirmedHtml(order) {
    var lines = order.itemsJson.map(function (item) {
      return '<div class="flex justify-between items-start text-xs border-b border-[#DCDAD4] pb-2 last:border-0 last:pb-0"><div>' +
        '<p class="font-bold text-[#101010]">' + item.quantity + "× " + esc(item.name) + "</p>" +
        '<p class="text-neutral-500 font-mono-spec text-[11px]">' + esc(item.option) + " · SKU " + esc(item.sku) + "</p></div>" +
        '<span class="font-mono-spec font-semibold">' + money(item.priceCents * item.quantity) + "</span></div>";
    }).join("");
    return '<div class="space-y-6"><div class="bg-[#101010] text-white p-6 border-l-4 border-[#E3262E]">' +
      '<span class="text-[11px] font-mono-spec uppercase tracking-widest text-[#E3262E]">DISPATCH QUEUED // LOT VERIFIED</span>' +
      '<h3 class="font-display text-3xl font-extrabold uppercase mt-1">ORDER #' + esc(order.orderNumber) + "</h3>" +
      '<p class="text-xs text-neutral-300 mt-2 leading-relaxed">Confirmation sent to <strong class="text-white">' + esc(order.customerEmail) + "</strong>. Your sealed items are queued for same-day warehouse fulfillment.</p></div>" +
      '<div class="border border-[#DCDAD4] bg-[#F4F3F0] p-4 space-y-3"><div class="text-xs font-mono-spec uppercase tracking-wider text-neutral-500">ITEMIZED MANIFEST</div>' + lines +
        '<div class="pt-2 border-t border-[#101010] flex justify-between text-sm font-bold"><span>TOTAL CHARGED</span><span class="font-mono-spec">' + money(order.totalCents) + "</span></div></div>" +
      '<button type="button" data-action="close-modal" class="w-full py-3.5 bg-[#101010] text-white font-display text-lg font-bold uppercase tracking-wider hover:bg-[#E3262E] transition-colors cursor-pointer">CONTINUE SHOPPING</button></div>';
  }

  function submitPromo() {
    var c = ui.cart;
    var candidate = c.promoInput.trim().toUpperCase();
    var t = totals();
    if (!candidate) { setPromo(""); c.feedback = ""; }
    else if (candidate === "EARN15") { setPromo("EARN15"); c.feedback = "EARN15 applied: 15% performance discount active."; }
    else if (candidate === "STACK20") {
      if (t.subtotal >= 12000) { setPromo("STACK20"); c.feedback = "STACK20 applied: 20% stack savings active."; }
      else c.feedback = "STACK20 requires a $120.00+ cart subtotal.";
    } else c.feedback = "Unrecognized code. Try EARN15 for 15% off.";
    renderModal();
  }

  function placeOrder() {
    var c = ui.cart;
    c.error = "";
    c.submitting = true;
    renderModal();
    apiRequest("POST", "/orders", {
      customerName: c.form.name,
      customerEmail: c.form.email,
      shippingAddress: c.form.address,
      shippingCity: c.form.city,
      shippingCountry: c.form.country,
      promoCode: totals().code || null,
      items: state.cart.map(function (i) { return { productId: i.productId, option: i.option, quantity: i.quantity }; })
    }).then(function (res) {
      if (!res.ok) c.error = res.data.error || "Unable to complete checkout.";
      else if (res.data.order) {
        c.order = res.data.order;
        state.recentOrders.unshift(res.data.order);
        state.cart = [];
        saveCart();
        renderChrome();
        c.step = "confirmed";
      }
    }).catch(function () {
      c.error = "Network error while placing order. Please try again.";
    }).then(function () {
      c.submitting = false;
      if (ui.modal === "cart") renderModal();
    });
  }

  /* ---- quick view */

  function quickViewHtml() {
    var q = ui.quick;
    var p = findProduct(q.productId);
    if (!p) return "";
    var discount = discountPercent(p.priceCents, p.compareAtPriceCents);
    var accessory = p.category === "Gym Accessories";

    var facts = p.nutritionFactsJson.map(function (f) {
      return '<div class="py-1.5 flex items-center justify-between gap-3"><dt class="font-medium text-[#101010]">' + esc(f.label) + "</dt>" +
        '<dd class="font-mono-spec text-[11px] font-semibold text-right whitespace-nowrap">' + esc(f.amount) +
        (f.dailyValue ? '<span class="text-neutral-500 ml-2">(' + esc(f.dailyValue) + ")</span>" : "") + "</dd></div>";
    }).join("");

    var highlights = p.highlightsJson.map(function (h) {
      return '<li class="flex items-start gap-2.5 text-xs sm:text-sm text-[#101010] font-medium"><span class="mt-0.5 bg-[#101010] text-white p-0.5 shrink-0">' + icon("check", "w-3 h-3") + "</span><span>" + esc(h) + "</span></li>";
    }).join("");

    var options = p.flavorsJson.map(function (opt) {
      var active = q.option === opt;
      return '<button type="button" data-action="qv-option" data-option="' + esc(opt) + '" class="px-3.5 py-2 text-xs font-semibold uppercase tracking-wider border transition-colors cursor-pointer ' +
        (active ? "bg-[#101010] text-white border-[#101010]" : "bg-[#F4F3F0] text-[#101010] border-[#DCDAD4] hover:border-[#101010]") + '">' + esc(opt) + "</button>";
    }).join("");

    var inner = '<div class="relative w-full max-w-5xl bg-white text-[#101010] border border-[#101010] shadow-2xl my-auto max-h-[92vh] overflow-y-auto" data-stop data-scroll>' +
      '<div class="flex items-center justify-between bg-[#101010] text-white px-5 py-3 border-b border-[#252525]"><div class="flex flex-wrap items-center gap-3 text-[11px] font-mono-spec tracking-wider uppercase">' +
        '<span class="text-[#E3262E] font-semibold">● IN STOCK</span><span class="text-neutral-400">SKU: ' + esc(p.sku) + '</span><span class="hidden sm:inline text-neutral-400">| ' + esc(p.batchCode) + "</span></div>" +
        '<button type="button" data-action="close-modal" aria-label="Close quick view" class="flex items-center gap-1.5 text-xs uppercase tracking-widest text-neutral-300 hover:text-white transition-colors cursor-pointer"><span>CLOSE</span>' + icon("close") + "</button></div>" +
      '<div class="grid grid-cols-1 lg:grid-cols-12">' +
        '<div class="lg:col-span-5 bg-[#F4F3F0] border-b lg:border-b-0 lg:border-r border-[#DCDAD4] p-6 flex flex-col justify-between gap-6">' +
          '<div class="relative aspect-square w-full bg-[#101010] overflow-hidden border border-[#DCDAD4]"><img src="' + esc(p.imageUrl) + '" alt="' + esc(p.brand + " " + p.name) + '" class="w-full h-full object-cover">' +
            (discount ? '<span class="absolute top-3 left-3 bg-[#E3262E] text-white text-[11px] font-bold uppercase tracking-widest px-2.5 py-1">SAVE ' + discount + "%</span>" : "") +
            (p.badge ? '<span class="absolute top-3 right-3 bg-[#101010] text-white border border-white/20 text-[10px] font-mono-spec uppercase tracking-widest px-2.5 py-1">' + esc(p.badge) + "</span>" : "") + "</div>" +
          '<div class="bg-white border-2 border-[#101010] p-4"><div class="border-b-4 border-[#101010] pb-1.5 mb-2">' +
            '<h4 class="font-display text-2xl font-extrabold uppercase leading-none tracking-tight">' + (accessory ? "Technical Specifications" : "Supplement Facts") + "</h4>" +
            '<p class="text-[11px] font-mono-spec text-neutral-600 mt-1">' + esc(p.servingInfo) + "</p></div>" +
            '<div class="flex justify-between text-[10px] font-bold uppercase tracking-wider border-b border-[#101010] pb-1 mb-1"><span>' + (accessory ? "Parameter" : "Amount Per Serving") + "</span><span>" + (accessory ? "Spec" : "% DV") + "</span></div>" +
            '<dl class="divide-y divide-neutral-200 text-xs">' + facts + "</dl>" +
            (!accessory ? '<p class="text-[10px] text-neutral-500 mt-2 pt-1.5 border-t border-[#101010] leading-tight">** Daily Value (DV) not established. Zero proprietary blends; all active quantities disclosed to the milligram.</p>' : "") +
          "</div></div>" +
        '<div class="lg:col-span-7 p-6 sm:p-8 flex flex-col justify-between"><div>' +
          '<div class="flex items-center justify-between gap-4 mb-2"><span class="text-xs font-mono-spec font-semibold uppercase tracking-widest text-[#E3262E]">' + esc(p.brand) + " // " + esc(p.category) + "</span>" +
            '<button type="button" data-action="qv-review" class="flex items-center gap-1.5 text-xs font-medium hover:text-[#E3262E] transition-colors cursor-pointer"><span class="flex text-[#E3262E]">' + stars(Math.round(p.rating), "w-3.5 h-3.5") + "</span>" +
              '<span class="font-semibold">' + p.rating.toFixed(1) + '</span><span class="text-neutral-500 underline">(' + p.reviewCount + " reviews)</span></button></div>" +
          '<h2 id="quickview-product-title" class="font-display text-3xl sm:text-4xl font-extrabold uppercase leading-[0.96] text-[#101010]">' + esc(p.name) + "</h2>" +
          '<p class="text-sm font-medium text-neutral-600 mt-2">' + esc(p.subtitle) + "</p>" +
          '<div class="flex items-baseline gap-3 mt-5 pb-5 border-b border-[#DCDAD4]"><span class="font-display text-4xl font-extrabold text-[#101010]">' + money(p.priceCents) + "</span>" +
            (p.compareAtPriceCents ? '<span class="text-base text-neutral-400 line-through font-mono-spec">' + money(p.compareAtPriceCents) + "</span>" : "") +
            (discount ? '<span class="text-xs font-bold uppercase tracking-wider text-[#E3262E] bg-[#E3262E]/10 px-2 py-0.5">SAVE ' + discount + "% TODAY</span>" : "") + "</div>" +
          '<p class="text-sm leading-relaxed text-neutral-700 mt-5">' + esc(p.description) + "</p>" +
          '<ul class="mt-5 space-y-2">' + highlights + "</ul>" +
          '<div class="mt-6"><span class="block text-xs font-mono-spec font-semibold uppercase tracking-widest text-neutral-500 mb-2">' + (accessory ? "SELECT SPECIFICATION / SIZE" : "SELECT FLAVOR / FORMAT") + '</span><div class="flex flex-wrap gap-2">' + options + "</div></div></div>" +
          '<div class="mt-8 pt-6 border-t border-[#DCDAD4]"><div class="flex flex-col sm:flex-row gap-3">' +
            '<div class="flex items-center border border-[#101010] bg-[#F4F3F0] h-13 sm:w-36 justify-between px-2">' +
              '<button type="button" data-action="qv-qty" data-delta="-1" aria-label="Decrease quantity" class="w-9 h-9 flex items-center justify-center text-lg font-bold hover:text-[#E3262E] cursor-pointer">−</button>' +
              '<span class="font-mono-spec text-sm font-bold">' + q.qty + "</span>" +
              '<button type="button" data-action="qv-qty" data-delta="1" aria-label="Increase quantity" class="w-9 h-9 flex items-center justify-center text-lg font-bold hover:text-[#E3262E] cursor-pointer">+</button></div>' +
            '<button type="button" data-action="qv-add" class="flex-1 h-13 px-6 font-display text-lg font-bold uppercase tracking-wider transition-colors cursor-pointer flex items-center justify-center gap-2 ' + (q.added ? "bg-[#101010] text-white" : "bg-[#E3262E] text-white hover:bg-[#c41c23]") + '">' +
              (q.added ? icon("check", "w-5 h-5") + "<span>ADDED TO BAG</span>" : "<span>ADD TO CART — " + money(p.priceCents * q.qty) + "</span>") + "</button></div>" +
            (!accessory ? '<p class="text-[11px] text-neutral-500 mt-4 leading-snug">*Compliance Notice: Lawful dietary supplement. Statements have not been evaluated by the FDA. Not intended to diagnose, treat, cure, or prevent any disease.</p>' : "") +
          "</div></div></div></div>";
    return backdrop(inner, "flex items-center justify-center bg-black/80 p-3 sm:p-6 overflow-y-auto", MODAL_ID.quick);
  }

  function openQuickView(id) {
    var p = findProduct(id);
    if (!p) return;
    ui.quick = { productId: p.id, option: p.flavorsJson[0] || "Standard", qty: 1, added: false };
    openModal("quick");
  }

  /* ---- search overlay */

  function searchResults() {
    var q = ui.search.query.trim().toLowerCase();
    if (!q) return state.products.slice(0, 6);
    return state.products.filter(function (p) {
      return [p.name, p.brand, p.category, p.subtitle, p.description].some(function (s) { return s.toLowerCase().indexOf(q) > -1; });
    });
  }

  function searchResultsHtml() {
    var list = searchResults();
    if (list.length === 0) {
      return '<p class="text-sm text-neutral-600 py-8 text-center">No lawful supplements or equipment matched &ldquo;' + esc(ui.search.query) + "&rdquo;. Try searching by active ingredient or category.</p>";
    }
    return '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">' + list.map(function (p) {
      return '<div class="flex items-center gap-3.5 p-3 border border-[#DCDAD4] bg-[#F4F3F0] hover:border-[#101010] transition-colors">' +
        '<img src="' + esc(p.imageUrl) + '" alt="' + esc(p.name) + '" class="w-16 h-16 object-cover bg-[#101010] shrink-0"><div class="flex-1 min-w-0">' +
        '<span class="text-[10px] font-mono-spec font-semibold text-[#E3262E] uppercase">' + esc(p.brand) + "</span>" +
        '<h4 class="font-display text-lg font-bold uppercase leading-tight truncate">' + esc(p.name) + "</h4>" +
        '<div class="flex items-center justify-between mt-1.5"><span class="font-mono-spec text-xs font-bold">' + money(p.priceCents) + "</span><div class=\"flex gap-1.5\">" +
          '<button type="button" data-action="search-specs" data-id="' + p.id + '" class="px-2 py-1 text-[10px] font-bold uppercase border border-[#101010] hover:bg-[#101010] hover:text-white transition-colors cursor-pointer">SPECS</button>' +
          '<button type="button" data-action="search-add" data-id="' + p.id + '" class="px-2.5 py-1 text-[10px] font-bold uppercase bg-[#E3262E] text-white hover:bg-[#101010] transition-colors cursor-pointer">+ BAG</button>' +
        "</div></div></div></div>";
    }).join("") + "</div>";
  }

  function searchHtml() {
    var terms = SEARCH_TERMS.map(function (term) {
      return '<button type="button" data-action="search-term" data-term="' + esc(term) + '" class="px-2.5 py-1 bg-white border border-[#DCDAD4] hover:border-[#101010] text-[11px] font-mono-spec uppercase cursor-pointer">' + esc(term) + "</button>";
    }).join("");
    var inner = '<div class="w-full max-w-4xl bg-white border border-[#101010] shadow-2xl" data-stop>' +
      '<div class="flex items-center gap-3 px-5 py-4 bg-[#101010] text-white">' + icon("search", "w-5 h-5 text-[#E3262E] shrink-0") +
        '<input id="search-input" type="search" data-autofocus data-model="search.query" value="' + esc(ui.search.query) + '" placeholder="Search formulations, active ingredients, brands, or gear..." class="w-full bg-transparent text-white placeholder-neutral-400 text-base focus:outline-none">' +
        '<button type="button" data-action="close-modal" aria-label="Close search" class="text-neutral-400 hover:text-white p-1 cursor-pointer">' + icon("close", "w-5 h-5") + "</button></div>" +
      '<div class="px-5 py-3 bg-[#F4F3F0] border-b border-[#DCDAD4] flex flex-wrap items-center gap-2"><span class="text-[10px] font-mono-spec uppercase tracking-widest text-neutral-500 mr-1">POPULAR QUERIES:</span>' + terms + "</div>" +
      '<div id="search-results" class="p-5 max-h-[65vh] overflow-y-auto">' + searchResultsHtml() + "</div></div>";
    return backdrop(inner, "bg-black/80 flex items-start justify-center pt-10 sm:pt-16 p-4 overflow-y-auto", "");
  }

  function updateSearchResults() {
    var el = $("#search-results");
    if (el) el.innerHTML = searchResultsHtml();
  }

  /* ---- account / order lookup */

  function accountHtml() {
    var a = ui.account;
    var orders = a.orders.map(function (o) {
      var count = o.itemsJson.reduce(function (s, i) { return s + i.quantity; }, 0);
      return '<div class="border border-[#101010] bg-[#F4F3F0] p-4 text-xs space-y-2"><div class="flex items-center justify-between">' +
        '<span class="font-mono-spec font-bold text-sm text-[#101010]">#' + esc(o.orderNumber) + '</span><span class="bg-[#101010] text-white px-2 py-0.5 text-[10px] font-mono-spec uppercase">' + esc(o.status) + "</span></div>" +
        '<p class="text-neutral-600">Ship to: ' + esc(o.customerName) + " — " + esc(o.shippingCity) + ", " + esc(o.shippingCountry) + "</p>" +
        '<div class="pt-1.5 border-t border-[#DCDAD4] flex justify-between font-semibold"><span>' + count + ' Items</span><span class="font-mono-spec">' + money(o.totalCents) + "</span></div></div>";
    }).join("");
    var empty = '<div class="bg-[#F4F3F0] border border-[#DCDAD4] p-4 text-xs text-neutral-600">' + (a.searched ? "No matching orders found for that email or order number." : "Place an order via your Training Bag or search your checkout email above to inspect live fulfillment status.") + "</div>";

    var inner = '<div class="w-full max-w-xl bg-white border border-[#101010] shadow-2xl" data-stop>' +
      '<div class="flex items-center justify-between px-6 py-4 bg-[#101010] text-white"><div><span class="text-[10px] font-mono-spec uppercase tracking-widest text-[#E3262E]">ATHLETE PORTAL // LOT TRACEABILITY</span>' +
        '<h2 id="account-modal-title" class="font-display text-2xl font-extrabold uppercase">ACCOUNT &amp; ORDER DISPATCH LOOKUP</h2></div>' +
        '<button type="button" data-action="close-modal" aria-label="Close account modal" class="text-neutral-400 hover:text-white cursor-pointer">' + icon("close", "w-5 h-5") + "</button></div>" +
      '<div class="p-6 space-y-5"><form data-form="lookup" class="space-y-2"><label for="lookup-input" class="block text-xs font-mono-spec font-semibold uppercase tracking-wider text-neutral-600">LOOK UP EXISTING ORDER BY EMAIL OR ORDER # (E.G. AXM-123456)</label>' +
        '<div class="flex gap-2"><input id="lookup-input" type="text" data-autofocus data-model="account.lookup" value="' + esc(a.lookup) + '" placeholder="athlete@domain.com or AXM-..." class="flex-1 h-11 px-3.5 bg-[#F4F3F0] border border-[#DCDAD4] text-sm">' +
        '<button type="submit"' + (a.loading ? " disabled" : "") + ' class="px-5 h-11 bg-[#101010] text-white font-display text-base font-bold uppercase tracking-wider hover:bg-[#E3262E] transition-colors cursor-pointer">' + (a.loading ? "SEARCHING..." : "TRACK") + "</button></div></form>" +
        '<div class="border-t border-[#DCDAD4] pt-4"><h3 class="text-xs font-mono-spec font-semibold uppercase tracking-widest text-neutral-500 mb-3">VERIFIED DISPATCH HISTORY</h3>' +
        (a.orders.length === 0 ? empty : '<div class="space-y-3 max-h-64 overflow-y-auto">' + orders + "</div>") + "</div></div></div>";
    return backdrop(inner, "bg-black/80 flex items-center justify-center p-4 overflow-y-auto", MODAL_ID.account);
  }

  function lookupOrders() {
    var a = ui.account;
    var val = a.lookup.trim();
    if (!val) return;
    a.loading = true;
    a.searched = true;
    renderModal();
    var param = val.indexOf("@") > -1 ? "email=" + encodeURIComponent(val) : "orderNumber=" + encodeURIComponent(val);
    apiRequest("GET", "/orders?" + param).then(function (res) {
      a.orders = Array.isArray(res.data.orders) ? res.data.orders : [];
    }).catch(function () {
      a.orders = [];
    }).then(function () {
      a.loading = false;
      if (ui.modal === "account") renderModal();
    });
  }

  /* ---- article reader */

  function articleHtml() {
    var art = DATA.articles.find(function (a) { return a.id === ui.article.id; });
    if (!art) return "";
    var takeaways = art.keyTakeaways.map(function (t) {
      return '<li class="flex items-start gap-2.5 text-xs sm:text-sm font-medium text-neutral-800">' + icon("check", "w-4 h-4 text-[#E3262E] shrink-0 mt-0.5") + "<span>" + esc(t) + "</span></li>";
    }).join("");
    var sections = art.sections.map(function (s) {
      return '<div><h4 class="font-display text-xl font-bold uppercase text-[#101010] mb-1.5">' + esc(s.heading) + '</h4><p class="text-sm leading-relaxed text-neutral-700">' + esc(s.content) + "</p></div>";
    }).join("");
    var inner = '<article class="w-full max-w-3xl bg-white border border-[#101010] shadow-2xl max-h-[90vh] overflow-y-auto" data-stop>' +
      '<div class="flex items-center justify-between px-6 py-4 bg-[#101010] text-white sticky top-0 z-10"><span class="text-[11px] font-mono-spec uppercase tracking-widest text-[#E3262E]">' + esc(art.category) + " // " + esc(art.readTime) + "</span>" +
        '<button type="button" data-action="close-modal" aria-label="Close guide" class="text-neutral-400 hover:text-white flex items-center gap-1 text-xs uppercase cursor-pointer"><span>CLOSE</span>' + icon("close") + "</button></div>" +
      '<div class="aspect-[21/9] w-full bg-[#101010] overflow-hidden"><img src="' + esc(art.imageUrl) + '" alt="' + esc(art.title) + '" class="w-full h-full object-cover opacity-90"></div>' +
      '<div class="p-6 sm:p-8 space-y-6"><h2 id="article-modal-title" class="font-display text-3xl sm:text-4xl font-extrabold uppercase leading-[0.98]">' + esc(art.title) + "</h2>" +
        '<div class="bg-[#F4F3F0] border-l-4 border-[#E3262E] p-5"><h3 class="text-xs font-mono-spec font-bold uppercase tracking-widest text-[#101010] mb-2.5">CLINICAL PROTOCOL TAKEAWAYS</h3><ul class="space-y-2">' + takeaways + "</ul></div>" +
        '<div class="space-y-5">' + sections + "</div>" +
        '<div class="pt-6 border-t border-[#DCDAD4] flex flex-col sm:flex-row sm:items-center justify-between gap-4"><p class="text-[11px] text-neutral-500 max-w-md">Educational briefing for healthy adult athletes. Consult a sports dietitian or physician for individualized clinical guidance.</p>' +
          '<button type="button" data-action="article-shop" data-category="' + esc(art.recommendedCategory) + '" class="px-6 py-3 bg-[#E3262E] text-white font-display text-base font-bold uppercase tracking-wider hover:bg-[#101010] transition-colors shrink-0 cursor-pointer">SHOP ' + esc(art.recommendedCategory) + " →</button></div></div></article>";
    return backdrop(inner, "bg-black/80 flex items-center justify-center p-3 sm:p-6 overflow-y-auto", MODAL_ID.article);
  }

  /* ---- review form */

  function reviewHtml() {
    var r = ui.review;
    var options = state.products.map(function (p) {
      return '<option value="' + p.id + '"' + (p.id === r.productId ? " selected" : "") + ">" + esc(p.brand + " — " + p.name) + "</option>";
    }).join("");
    var starBtns = [1, 2, 3, 4, 5].map(function (n) {
      return '<button type="button" data-action="review-rating" data-rating="' + n + '" aria-label="Rate ' + n + ' out of 5 stars" class="p-2 border transition-colors cursor-pointer ' +
        (n <= r.rating ? "bg-[#101010] text-[#E3262E] border-[#101010]" : "bg-[#F4F3F0] text-neutral-400 border-[#DCDAD4]") + '">' + icon("star", "w-4 h-4", n <= r.rating) + "</button>";
    }).join("");
    var lbl = "block text-[11px] font-mono-spec font-semibold uppercase tracking-wider mb-1";
    var inner = '<div class="w-full max-w-lg bg-white border border-[#101010] shadow-2xl" data-stop>' +
      '<div class="flex items-center justify-between px-6 py-4 bg-[#101010] text-white"><h2 id="write-review-title" class="font-display text-2xl font-extrabold uppercase">SUBMIT ATHLETE FIELD REVIEW</h2>' +
        '<button type="button" data-action="close-modal" aria-label="Close review form" class="text-neutral-400 hover:text-white cursor-pointer">' + icon("close", "w-5 h-5") + "</button></div>" +
      '<form data-form="review" class="p-6 space-y-4">' +
        (r.error ? '<div class="bg-[#E3262E]/10 border border-[#E3262E] text-[#E3262E] p-3 text-xs font-semibold" role="alert">' + esc(r.error) + "</div>" : "") +
        '<div><label class="' + lbl + '">PRODUCT REVIEWED</label><select data-model="review.productId" data-number class="w-full h-11 px-3 bg-[#F4F3F0] border border-[#DCDAD4] text-sm font-medium">' + options + "</select></div>" +
        '<div><span class="' + lbl + '">PERFORMANCE RATING</span><div class="flex items-center gap-2">' + starBtns + '<span class="text-xs font-mono-spec font-semibold ml-2">' + r.rating + ".0 / 5.0</span></div></div>" +
        '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">' +
          '<div><label class="' + lbl + '">YOUR NAME *</label><input type="text" required data-model="review.authorName" value="' + esc(r.authorName) + '" placeholder="Jordan Hale" class="w-full h-10 px-3 bg-[#F4F3F0] border border-[#DCDAD4] text-sm"></div>' +
          '<div><label class="' + lbl + '">TRAINING DISCIPLINE</label><input type="text" data-model="review.athleteRole" value="' + esc(r.athleteRole) + '" placeholder="e.g. Powerlifter / Coach" class="w-full h-10 px-3 bg-[#F4F3F0] border border-[#DCDAD4] text-sm"></div></div>' +
        '<div><label class="' + lbl + '">REVIEW HEADLINE *</label><input type="text" required data-model="review.title" value="' + esc(r.title) + '" placeholder="Clean mixability and verified dosing" class="w-full h-10 px-3 bg-[#F4F3F0] border border-[#DCDAD4] text-sm"></div>' +
        '<div><label class="' + lbl + '">FIELD NOTES &amp; EXPERIENCE *</label><textarea rows="3" required data-model="review.body" placeholder="Share mixability, digestion, training output, or build quality..." class="w-full p-3 bg-[#F4F3F0] border border-[#DCDAD4] text-sm">' + esc(r.body) + "</textarea></div>" +
        '<button type="submit"' + (r.submitting ? " disabled" : "") + ' class="w-full h-12 bg-[#E3262E] text-white font-display text-lg font-bold uppercase tracking-wider hover:bg-[#101010] transition-colors cursor-pointer">' + (r.submitting ? "PUBLISHING REVIEW..." : "PUBLISH VERIFIED REVIEW") + "</button></form></div>";
    return backdrop(inner, "bg-black/80 flex items-center justify-center p-4 overflow-y-auto", MODAL_ID.review);
  }

  function openReview(productId) {
    ui.review.productId = productId || (state.products[0] && state.products[0].id) || 1;
    ui.review.error = "";
    openModal("review");
  }

  function submitReview() {
    var r = ui.review;
    r.error = "";
    r.submitting = true;
    renderModal();
    apiRequest("POST", "/reviews", {
      productId: r.productId, rating: r.rating, authorName: r.authorName,
      athleteRole: r.athleteRole || "Verified Training Customer", title: r.title, body: r.body
    }).then(function (res) {
      if (!res.ok) { r.error = res.data.error || "Unable to submit review."; return; }
      if (res.data.review) {
        var review = res.data.review;
        state.reviews.unshift(review);
        var p = review.productId ? findProduct(review.productId) : null;
        if (p) {
          var nextCount = p.reviewCount + 1;
          p.rating = Number(((p.rating * p.reviewCount + review.rating) / nextCount).toFixed(1));
          p.reviewCount = nextCount;
        }
        r.authorName = r.athleteRole = r.title = r.body = "";
        renderReviews();
        renderProducts();
        ui.modal = null;
      }
    }).catch(function () {
      r.error = "Network error while submitting review.";
    }).then(function () {
      r.submitting = false;
      renderModal();
    });
  }

  /* ---- policy */

  function policyHtml() {
    var doc = DATA.policies[ui.policy.key];
    if (!doc) return "";
    var inner = '<div class="w-full max-w-2xl bg-white border border-[#101010] shadow-2xl" data-stop>' +
      '<div class="flex items-center justify-between px-6 py-4 bg-[#101010] text-white"><div><span class="text-[10px] font-mono-spec uppercase tracking-widest text-[#E3262E]">AXIOM LEGAL &amp; COMPLIANCE // ' + esc(doc.updated) + "</span>" +
        '<h2 id="policy-modal-title" class="font-display text-2xl font-extrabold uppercase">' + esc(doc.title) + "</h2></div>" +
        '<button type="button" data-action="close-modal" aria-label="Close policy document" class="text-neutral-400 hover:text-white cursor-pointer">' + icon("close", "w-5 h-5") + "</button></div>" +
      '<div class="p-6 space-y-4 text-sm text-neutral-700 leading-relaxed">' +
        doc.paragraphs.map(function (p) { return '<p class="bg-[#F4F3F0] border-l-2 border-[#101010] p-4">' + esc(p) + "</p>"; }).join("") +
        '<div class="pt-2 flex justify-end"><button type="button" data-action="close-modal" class="px-6 py-2.5 bg-[#101010] text-white font-display text-base font-bold uppercase tracking-wider hover:bg-[#E3262E] transition-colors cursor-pointer">ACKNOWLEDGE &amp; CLOSE</button></div></div></div>';
    return backdrop(inner, "bg-black/80 flex items-center justify-center p-4 overflow-y-auto", MODAL_ID.policy);
  }

  /* ----------------------------------------------------------- newsletter */

  function submitNewsletter(form) {
    var btn = $("#newsletter-submit");
    var status = $("#newsletter-status");
    var emailInput = $("#newsletter-email");
    var label = btn.textContent;
    btn.disabled = true;
    btn.textContent = "ACTIVATING BRIEFING...";
    status.textContent = "";

    apiRequest("POST", "/newsletter", { email: emailInput.value, trainingFocus: $("#newsletter-focus").value })
      .then(function (res) {
        if (!res.ok) {
          status.className = "text-xs font-mono-spec text-[#E3262E]";
          status.textContent = res.data.error || "Unable to subscribe.";
        } else {
          setPromo("EARN15");
          emailInput.value = "";
          status.className = "text-xs font-mono-spec text-emerald-400";
          status.textContent = "✓ " + (res.data.message || "Subscribed. Code EARN15 automatically applied to your Training Bag.");
        }
      })
      .catch(function () {
        status.className = "text-xs font-mono-spec text-[#E3262E]";
        status.textContent = "Connection error. Please try again.";
      })
      .then(function () {
        btn.disabled = false;
        btn.textContent = label;
      });
  }

  /* --------------------------------------------------------------- events */

  document.addEventListener("click", function (e) {
    var el = e.target.closest("[data-action]");
    if (!el) return;
    var d = el.dataset;

    switch (d.action) {
      case "backdrop":
        if (e.target === el) closeModal();
        break;
      case "close-modal": closeModal(); break;

      case "home":
        e.preventDefault();
        state.category = "ALL";
        state.brand = "ALL";
        renderCatalog();
        window.scrollTo({ top: 0, behavior: "smooth" });
        break;
      case "toggle-mobile-menu": setMobileMenu($("#mobile-navigation").classList.contains("hidden")); break;
      case "close-mobile-menu": setMobileMenu(false); break;

      case "shop": scrollToCatalog(d.category, d.brand !== undefined ? d.brand : "ALL"); break;
      case "set-category": state.category = d.category; renderCatalog(); break;
      case "set-brand": state.brand = d.brand; renderCatalog(); break;
      case "clear-brand": state.brand = "ALL"; renderCatalog(); break;
      case "reset-filters": state.category = "ALL"; state.brand = "ALL"; renderCatalog(); break;
      case "build-custom-stack": setPromo("EARN15"); scrollToCatalog("ALL", "ALL"); break;

      case "apply-earn15": setPromo("EARN15"); break;
      case "add-stack": addPerformanceStack(); break;
      case "add-to-cart": { var p = findProduct(d.id); if (p) addToCart(p); break; }
      case "quick-view": openQuickView(d.id); break;

      case "open-cart": ui.cart.promoInput = state.promo; openModal("cart"); break;
      case "cart-checkout": ui.cart.step = "checkout"; ui.cart.error = ""; renderModal(); break;
      case "cart-back": ui.cart.step = "cart"; renderModal(); break;
      case "cart-qty": updateQty(d.key, Number(d.delta)); break;
      case "cart-remove": removeItem(d.key); break;
      case "cart-apply-earn15":
        setPromo("EARN15");
        ui.cart.feedback = "EARN15 applied: 15% performance discount active.";
        renderModal();
        break;

      case "qv-option": ui.quick.option = d.option; renderModal(); break;
      case "qv-qty": ui.quick.qty = Math.min(15, Math.max(1, ui.quick.qty + Number(d.delta))); renderModal(); break;
      case "qv-add": {
        var prod = findProduct(ui.quick.productId);
        if (!prod) break;
        addToCart(prod, ui.quick.option, ui.quick.qty);
        ui.quick.added = true;
        renderModal();
        setTimeout(function () {
          ui.quick.added = false;
          if (ui.modal === "quick") renderModal();
        }, 1600);
        break;
      }
      case "qv-review": { var pid = ui.quick.productId; ui.modal = null; openReview(pid); break; }

      case "open-search": ui.search.query = ""; openModal("search"); break;
      case "search-term":
        ui.search.query = d.term;
        $("#search-input").value = d.term;
        updateSearchResults();
        break;
      case "search-specs": ui.modal = null; openQuickView(d.id); break;
      case "search-add": { var sp = findProduct(d.id); if (sp) addToCart(sp); closeModal(); break; }

      case "open-account": ui.account.orders = state.recentOrders.slice(); ui.account.searched = false; openModal("account"); break;

      case "open-article": ui.article.id = d.id; openModal("article"); break;
      case "article-shop": ui.modal = null; renderModal(); scrollToCatalog(d.category, "ALL"); break;

      case "open-review": openReview(null); break;
      case "review-rating": ui.review.rating = Number(d.rating); renderModal(); break;

      case "open-policy": ui.policy.key = d.policy; openModal("policy"); break;
    }
  });

  document.addEventListener("input", function (e) {
    var el = e.target;
    if (!el.dataset || !el.dataset.model) return;
    setPath(ui, el.dataset.model, el.hasAttribute("data-number") ? Number(el.value) : el.value);
    if (el.dataset.model === "search.query") updateSearchResults();
  });

  // <select data-model> fires "change" reliably in all browsers; mirror the input handler
  document.addEventListener("change", function (e) {
    var el = e.target;
    if (el.tagName === "SELECT" && el.dataset && el.dataset.model) {
      setPath(ui, el.dataset.model, el.hasAttribute("data-number") ? Number(el.value) : el.value);
    }
  });

  document.addEventListener("submit", function (e) {
    var form = e.target;
    if (form.id === "newsletter-form") {
      e.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }
      submitNewsletter(form);
      return;
    }
    var kind = form.dataset && form.dataset.form;
    if (!kind) return;
    e.preventDefault();
    if (kind === "promo") submitPromo();
    else if (kind === "checkout") placeOrder();
    else if (kind === "lookup") lookupOrders();
    else if (kind === "review") submitReview();
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && ui.modal) closeModal();
  });

  $("#sort-select").addEventListener("change", function (e) {
    state.sortBy = e.target.value;
    renderProducts();
  });

  /* ----------------------------------------------------------------- boot */

  loadCart();
  ui.cart.promoInput = state.promo;
  $("#year").textContent = new Date().getFullYear();
  renderChrome();
  renderCatalog();
  renderGuides();
  renderReviews();
  renderFooterCategories();
  renderStructuredData();
})();
