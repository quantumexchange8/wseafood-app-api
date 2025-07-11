<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Events\ContentViewedEvent;
use App\Models\Category;
use App\Models\Product;

final readonly class ProductQuery
{
    /** @param  array{}  $args */
    public function getCategories(null $_, array $args): array
    {
        $query = Category::with('media')
            ->where('status', 'active');

        return [
            'success' => true,
            'message' => [trans('public.successfully_fetched_categories')],
            'categories' => $query->get(),
        ];
    }

    /** @param  array{}  $args */
    public function getProducts(null $_, array $args): array
    {
        $query = Product::with(['category', 'media'])
            ->where('status', 'active');

        if (!empty($args['category_id'])) {
            // Check if category exists
            $categoryExists = Category::where('id', $args['category_id'])
                ->where('status', 'active')
                ->exists();

            if (!$categoryExists) {
                return [
                    'success' => false,
                    'message' => [trans('public.category_not_found')],
                    'products' => [],
                ];
            }

            $query->where('category_id', $args['category_id']);
        }

        return [
            'success' => true,
            'message' => [trans('public.successfully_fetched_products')],
            'products' => $query->get(),
        ];
    }

    /** @param array{} $args
     */
    public function getProductDetail(null $_, array $args): array
    {
        $product = Product::with(['category', 'media'])
            ->where('status', 'active')
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
}
