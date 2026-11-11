<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    private const SORTS = [
        'featured' => 'Featured',
        'newest' => 'Newest',
        'price_asc' => 'Price: Low to High',
        'price_desc' => 'Price: High to Low',
    ];

    public function index(Request $request, ?Category $category = null): View|JsonResponse
    {
        abort_if($category && ! $category->is_active, 404);

        $categoryIds = $category?->descendantIds();
        $scope = fn (Builder $q) => $q->active()
            ->when($categoryIds, fn ($q) => $q->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $categoryIds)));

        $selected = [
            'brand' => array_filter((array) $request->query('brand')),
            'attr' => collect((array) $request->query('attr'))->map(fn ($v) => array_filter((array) $v))->filter()->all(),
            'min' => $request->filled('min') ? max(0, (float) $request->query('min')) : null,
            'max' => $request->filled('max') ? max(0, (float) $request->query('max')) : null,
            'in_stock' => $request->boolean('in_stock'),
            'q' => trim((string) $request->query('q')),
        ];
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'featured';

        $products = $scope(Product::query())
            ->with(['brand:id,name', 'variants'])
            ->when($selected['q'], fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%")))
            ->when($selected['brand'], fn ($q, $slugs) => $q->whereHas('brand', fn ($b) => $b->whereIn('slug', $slugs)))
            ->when($selected['min'] !== null, fn ($q) => $q->where('max_price', '>=', $selected['min']))
            ->when($selected['max'] !== null, fn ($q) => $q->where('min_price', '<=', $selected['max']))
            ->when($selected['in_stock'], fn ($q) => $q->where(fn ($w) => $w
                ->where(fn ($s) => $s->where('type', 'simple')->where('stock_status', 'in_stock'))
                ->orWhere(fn ($v) => $v->where('type', 'variable')->whereHas('variants', fn ($pv) => $pv->where('is_active', true)->where('stock_qty', '>', 0)))));

        // One whereHas per attribute: AND between attributes, OR within an attribute.
        foreach ($selected['attr'] as $attributeSlug => $valueSlugs) {
            $products->whereHas('attributeValues', fn ($q) => $q
                ->whereIn('attribute_values.slug', $valueSlugs)
                ->whereHas('attribute', fn ($a) => $a->where('slug', $attributeSlug)));
        }

        match ($sort) {
            'newest' => $products->latest('id'),
            'price_asc' => $products->orderBy('min_price')->orderBy('id'),
            'price_desc' => $products->orderByDesc('min_price')->orderBy('id'),
            default => $products->orderByDesc('is_featured')->orderByDesc('id'),
        };

        $paginator = $products->paginate(config('shop.per_page'))->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('shop._cards', ['products' => $paginator])->render(),
                'next' => $paginator->nextPageUrl(),
            ]);
        }

        $brands = Brand::where('is_active', true)
            ->withCount(['products' => fn ($q) => $scope($q)])
            ->orderBy('name')->get()->filter(fn ($b) => $b->products_count > 0);

        $attributes = Attribute::where('is_filterable', true)->orderBy('sort_order')
            ->with(['values' => fn ($q) => $q->withCount(['products' => fn ($p) => $scope($p)])])
            ->get()
            ->each(fn ($a) => $a->setRelation('values', $a->values->filter(fn ($v) => $v->products_count > 0)->values()))
            ->filter(fn ($a) => $a->values->isNotEmpty());

        $subcategories = ($category
            ? $category->children()->where('is_active', true)->get()
            : Category::active()->whereNull('parent_id')->orderBy('sort_order')->get());

        $bounds = $scope(Product::query())->selectRaw('MIN(min_price) as lo, MAX(max_price) as hi')->first();

        return view('shop.index', [
            'category' => $category,
            'subcategories' => $subcategories,
            'products' => $paginator,
            'brands' => $brands,
            'attributes' => $attributes,
            'selected' => $selected,
            'sort' => $sort,
            'sorts' => self::SORTS,
            'bounds' => ['lo' => (int) floor($bounds->lo ?? 0), 'hi' => (int) ceil($bounds->hi ?? 0)],
        ]);
    }
}
