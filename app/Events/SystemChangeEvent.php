<?php

namespace App\Events;

use App\Models\SystemChange;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SystemChangeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $systemChange;

    /**
     * Create a new event instance.
     */
    public function __construct(SystemChange $systemChange)
    {
        $this->systemChange = $systemChange;
    }
}
