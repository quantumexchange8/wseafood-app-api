<?php

namespace App\Listeners;

use App\Events\ContentViewedEvent;
use App\Models\ViewAnalytic;
use Auth;

class LogContentViewListener
{
    public function __construct()
    {
    }

    public function handle(ContentViewedEvent $event): void
    {
        ViewAnalytic::create([
            'subject_type' => get_class($event->subject),
            'subject_id'   => $event->subject->id,
            'user_id'      => Auth::id(),
            'type'         => $event->type,
            'ip_address'   => request()->ip(),
        ]);
    }
}
