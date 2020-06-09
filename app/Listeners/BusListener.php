<?php

namespace App\Listeners;

use App\Events\BusEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BusListener implements ShouldQueue
{
    public $queue = 'event_bus';
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(Bus $event)
    {
        //
    }
}
