<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\PointLog;
use Auth;

final readonly class PointQuery
{
    /** @param  array{}  $args */
    public function getPointHistory(null $_, array $args)
    {
        return PointLog::where([
            'user_id' => Auth::id(),
            'adjust_type' => $args['type'],
        ])->latest()->get();
    }
}
