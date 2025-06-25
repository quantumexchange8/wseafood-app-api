<?php

namespace App\Providers;

use App\Events\ContentViewedEvent;
use App\Listeners\LogContentViewListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
    }

    protected array $listen = [
        ContentViewedEvent::class => [
            LogContentViewListener::class,
        ],
    ];
}
