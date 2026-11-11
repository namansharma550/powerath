<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): array
    {
        $cat = Category::create(['name' => 'Protein']);
        $brand = Brand::create(['name' => 'Acme', 'slug' => 'acme']);
        $flavour = Attribute::create(['name' => 'Flavour', 'slug' => 'flavour']);
        $choc = AttributeValue::create(['attribute_id' => $flavour->id, 'value' => 'Chocolate', 'slug' => 'chocolate']);
        $van = AttributeValue::create(['attribute_id' => $flavour->id, 'value' => 'Vanilla', 'slug' => 'vanilla']);

        $simple = Product::create(['name' => 'Shaker', 'price' => 300, 'sale_price' => 250, 'stock_qty' => 5, 'brand_id' => $brand->id]);
        $simple->categories()->attach($cat);

        $variable = Product::create(['name' => 'Whey', 'type' => 'variable', 'brand_id' => $brand->id]);
        $variable->categories()->attach($cat);
        $vChoc = $variable->variants()->create(['price' => 2000, 'stock_qty' => 3]);
        $vChoc->attributeValues()->sync([$choc->id]);
        $vVan = $variable->variants()->create(['price' => 1800, 'stock_qty' => 0]);
        $vVan->attributeValues()->sync([$van->id]);
        $variable->attributeValues()->sync([$choc->id, $van->id]);

        return compact('cat', 'simple', 'variable', 'vChoc', 'vVan', 'choc');
    }

    public function test_price_range_is_cached_for_both_product_types(): void
    {
        $d = $this->seedCatalog();

        $this->assertEquals(250, $d['simple']->fresh()->min_price);
        $this->assertEquals(1800, $d['variable']->fresh()->min_price);
        $this->assertEquals(2000, $d['variable']->fresh()->max_price);
    }

    public function test_listing_filters_by_category_attribute_and_price(): void
    {
        $d = $this->seedCatalog();

        $this->get('/category/protein')->assertOk()->assertSee('Shaker')->assertSee('Whey');
        $this->get('/shop?attr[flavour][]=chocolate')->assertOk()->assertSee('Whey')->assertDontSee('Shaker');
        $this->get('/shop?max=500')->assertOk()->assertSee('Shaker')->assertDontSee('Whey');
        $this->get('/shop?brand[]=acme&sort=price_desc')->assertOk();

        $json = $this->getJson('/shop', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->assertStringContainsString('Shaker', $json['html']);
    }

    public function test_product_pages_render(): void
    {
        $this->seedCatalog();

        $this->get('/product/shaker')->assertOk()->assertSee('Add to cart');
        $this->get('/product/whey')->assertOk()->assertSee('Flavour');
        $this->get('/')->assertOk();
    }

    public function test_variable_product_requires_a_variant_and_respects_stock(): void
    {
        $d = $this->seedCatalog();

        $this->post('/cart/add', ['product_id' => $d['variable']->id])->assertSessionHas('error');
        $this->post('/cart/add', ['product_id' => $d['variable']->id, 'variant_id' => $d['vVan']->id])->assertSessionHas('error');
        $this->post('/cart/add', ['product_id' => $d['variable']->id, 'variant_id' => $d['vChoc']->id, 'quantity' => 9])
            ->assertSessionHas('error');
        $this->post('/cart/add', ['product_id' => $d['variable']->id, 'variant_id' => $d['vChoc']->id, 'quantity' => 2])
            ->assertRedirect('/cart');
    }

    public function test_full_guest_checkout_with_coupon_decrements_stock(): void
    {
        $d = $this->seedCatalog();
        Coupon::create(['code' => 'save10', 'type' => 'percent', 'value' => 10]);

        $this->post('/cart/add', ['product_id' => $d['simple']->id, 'quantity' => 2]);
        $this->post('/cart/coupon', ['code' => 'bogus'])->assertSessionHas('error');
        $this->post('/cart/coupon', ['code' => 'SAVE10'])->assertSessionHas('success');
        $this->get('/cart')->assertOk()->assertSee('SAVE10');

        $this->get('/checkout')->assertOk();

        $res = $this->post('/checkout', [
            'name' => 'Test User', 'email' => 'T@Example.com', 'phone' => '9999999999',
            'line1' => '1 Street', 'city' => 'Pune', 'state' => 'MH', 'postcode' => '411001',
            'payment_method' => 'cod',
        ]);

        $order = Order::firstOrFail();
        $res->assertRedirect("/orders/{$order->order_number}");

        // 2 × 250 = 500, 10% off = 50, shipping 79 (under free-shipping threshold) → 529
        $this->assertEquals(500, $order->subtotal);
        $this->assertEquals(50, $order->discount);
        $this->assertEquals(79, $order->shipping);
        $this->assertEquals(529, $order->total);
        $this->assertSame('t@example.com', $order->customer_email);
        $this->assertEquals(3, $d['simple']->fresh()->stock_qty);
        $this->assertEquals(1, Coupon::first()->used_count);

        $this->get("/orders/{$order->order_number}")->assertOk()->assertSee($order->order_number);
        $this->get('/cart')->assertSee('Your cart is empty');
    }

    public function test_other_visitors_cannot_view_an_order(): void
    {
        $order = Order::create([
            'order_number' => 'PA000000X', 'customer_name' => 'A', 'customer_email' => 'a@b.com', 'customer_phone' => '1',
            'shipping_address' => ['name' => 'A'], 'subtotal' => 1, 'total' => 1,
        ]);

        $this->get('/orders/PA000000X')->assertNotFound();
    }

    public function test_register_login_and_account_pages(): void
    {
        $this->post('/register', ['name' => 'Jane', 'email' => 'jane@example.com', 'password' => 'secret-pass', 'password_confirmation' => 'secret-pass'])
            ->assertRedirect('/account');
        $this->get('/account')->assertOk()->assertSee('Jane');
        $this->get('/account/orders')->assertOk();
        $this->post('/logout');

        $this->get('/account')->assertRedirect('/login');
        $this->post('/login', ['email' => 'jane@example.com', 'password' => 'wrong'])->assertSessionHas('error');
        $this->post('/login', ['email' => 'jane@example.com', 'password' => 'secret-pass'])->assertRedirect();
    }

    public function test_guest_cart_is_kept_after_login(): void
    {
        $d = $this->seedCatalog();
        User::create(['name' => 'Jo', 'email' => 'jo@example.com', 'password' => 'secret-pass']);

        $this->post('/cart/add', ['product_id' => $d['simple']->id]);
        $this->post('/login', ['email' => 'jo@example.com', 'password' => 'secret-pass']);

        $this->get('/cart')->assertSee('Shaker');
    }

    public function test_homepage_shows_sections_and_bestsellers_lead_with_most_ordered(): void
    {
        $d = $this->seedCatalog();
        $order = Order::create([
            'order_number' => 'PA1', 'customer_name' => 'A', 'customer_email' => 'a@b.com', 'customer_phone' => '1',
            'shipping_address' => ['name' => 'A'], 'subtotal' => 1, 'total' => 1,
        ]);
        $order->items()->create(['product_id' => $d['simple']->id, 'name' => 'Shaker', 'price' => 250, 'quantity' => 4, 'total' => 1000]);

        $html = $this->get('/')->assertOk()
            ->assertSee('Most selling products')->assertSee('Shop by series')->assertSee('Frequently asked questions')
            ->assertSee('Sign up to receive exclusive deals!')->assertSee('Fuel for champions')
            ->getContent();

        // "Shaker" (4 sold) must appear before "Whey" in the bestseller row.
        $section = substr($html, strpos($html, 'Most selling products'));
        $this->assertLessThan(strpos($section, 'Whey'), strpos($section, 'Shaker'));
    }

    public function test_blog_only_shows_published_posts(): void
    {
        $live = \App\Models\Post::create(['title' => 'Live Post', 'body' => '<p>Hello</p>', 'is_published' => true]);
        \App\Models\Post::create(['title' => 'Draft Post', 'body' => '<p>x</p>', 'is_published' => false]);
        \App\Models\Post::create(['title' => 'Future Post', 'body' => '<p>x</p>', 'is_published' => true, 'published_at' => now()->addDay()]);

        $this->get('/blog')->assertOk()->assertSee('Live Post')->assertDontSee('Draft Post')->assertDontSee('Future Post');
        $this->get('/blog/live-post')->assertOk()->assertSee('Hello', false);
        $this->get('/blog/draft-post')->assertNotFound();
        $this->get('/blog/future-post')->assertNotFound();
        $this->get('/')->assertOk()->assertSee('From the blog')->assertSee('Live Post');
    }

    public function test_newsletter_signup_is_idempotent_and_validated(): void
    {
        $this->post('/newsletter', ['email' => 'Fan@Example.com'])->assertSessionHas('newsletter');
        $this->post('/newsletter', ['email' => 'fan@example.com'])->assertSessionHas('newsletter');
        $this->post('/newsletter', ['email' => 'nope'])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertDatabaseHas('subscribers', ['email' => 'fan@example.com']);
    }

    public function test_footer_content_pages_exist(): void
    {
        foreach (array_keys(config('pages.pages')) as $slug) {
            $this->get("/page/{$slug}")->assertOk();
        }
        $this->get('/page/does-not-exist')->assertNotFound();
    }

    public function test_customers_cannot_access_admin_panel(): void
    {
        $user = User::create(['name' => 'Jo', 'email' => 'jo@example.com', 'password' => 'secret-pass']);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
