<?php

namespace App\Events;

class BusEvent extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $data;
    public $event_name;

    public function __construct($event_name, $data = array())
    {
        $this->data = $data;
        $this->event_name = $event_name.'::employee_ticket';
    }
}