<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ContentViewedEvent
{
    use Dispatchable;

    public $subject;
    public $type;

    public function __construct($subject, $type)
    {
        $this->subject = $subject;
        $this->type = $type;
    }
}
