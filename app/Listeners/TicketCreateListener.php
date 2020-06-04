<?php

namespace App\Listeners;

#use App\Events\ExampleEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Ticket;
use App\TicketType;

class TicketCreateListener
{
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
        // check ticket type 
        $ticketModel = new Ticket;
        $ticketTypeModel = new TicketType;

        $ticketTypeDetail = $ticketTypeModel->getDetailById($data['type_id']);
        if (!$ticketTypeDetail) {
            \Log::warning("Ticket Type not found ");
            return false;
        }
        // tao ticket moi
        $ticketId =  $ticketModel->add($data);
        if (!$ticketId) {
            \Log::warning("Can not create ticket");
            return false;
        }
        /// assign ticket to manager
        $intLevel = $ticketTypeDetail->level_approve;
    }
}
