<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Events\ContentViewedEvent;
use App\Models\Highlight;

final readonly class HighlightQuery
{
    /** @param  array{}  $args */
    public function getHighlights(null $_, array $args)
    {
        return Highlight::with('media')->orderBy('position')->get();
    }

    /** @param array{} $args
     */
    public function getHighlightContent(null $_, array $args): array
    {
        $highlight = Highlight::with('media')
            ->find($args['highlight_id']);

        if (!$highlight) {
            return [
                'success' => false,
                'message' => ['Highlight not found'],
                'content' => null,
            ];
        }

        event(new ContentViewedEvent($highlight, 'highlight'));

        return [
            'success' => true,
            'message' => ['Success fetched highlight'],
            'content' => $highlight,
        ];
    }
}
