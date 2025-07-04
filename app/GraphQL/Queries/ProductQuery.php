<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Events\ContentViewedEvent;
use App\Models\Category;
use App\Models\Product;

final readonly class ProductQuery
{
    /** @param  array{}  $args */
    public function getProducts(null $_, array $args): array
    {
        $query = Product::with(['category', 'media']);

        if (!empty($args['category_id'])) {
            // Check if category exists
            $categoryExists = Category::where('id', $args['category_id'])->exists();

            if (!$categoryExists) {
                return [
                    'success' => false,
                    'message' => ['Category not found'],
                    'products' => [],
                ];
            }

            $query->where('category_id', $args['category_id']);
        }

        return [
            'success' => true,
            'message' => ['Success fetched products'],
            'products' => $query->get(),
        ];
    }

    /** @param array{} $args
     */
    public function getProductDetail(null $_, array $args): array
    {
        $product = Product::with(['category', 'media'])
            ->find($args['product_id']);

        if (!$product) {
            return [
                'success' => false,
                'message' => [trans('public.menu_item_not_found')],
                'detail' => null,
            ];
        }

        event(new ContentViewedEvent($product, 'detail'));

        return [
            'success' => true,
            'message' => [trans('public.successfully_fetched_menu_item')],
            'detail' => $product,
        ];
    }

    public function resolveCategoryName(Category $category, array $args)
    {
        return $category->getTranslation('name', app()->getLocale());
    }

    public function resolveProductName(Product $product, array $args)
    {
        return $product->getTranslation('name', app()->getLocale());
    }
}
