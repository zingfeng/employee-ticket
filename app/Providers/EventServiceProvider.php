<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\BusEvent' => [
            'App\Listeners\BusListener',
        ],
        'App\Events\TicketCreateEvent' => [
            'App\Listeners\TicketCreateListener',
        ],
    ];
    protected $subscribe = [
    ];
}
