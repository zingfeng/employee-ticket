<?php

namespace App\Listeners;

use App\Events\TicketCreateEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Http\Request;

class TicketCreateListener
{
    public $queue = 'employee_ticket';
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
    public function handle($data)
    {
        if ($data instanceof TicketCreateEvent) {
            $data = $data->data;
        }
        $request = new Request;
        // check ticket type 
        $request = $request->merge($data);
        $controller = new \App\Http\Controllers\TicketController;
        return $controller->add($request);
    }
}
