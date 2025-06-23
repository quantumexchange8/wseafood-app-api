<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use LaravelIdea\Helper\App\Models\_IH_Product_QB;

final readonly class ProductQuery
{
    /** @param  array{}  $args */
    public function getProducts(null $_, array $args): _IH_Product_QB|Builder
    {
        return Product::with(['category', 'media']);
    }
}
