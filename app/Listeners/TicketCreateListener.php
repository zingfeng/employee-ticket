<?php

namespace App\Listeners;

#use App\Events\ExampleEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Ticket;
use App\TicketType;
use App\TicketProcess;
use App\Employee;

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
        //Sau khi tạo ticket xong -> tạo process (nếu có)
        $i = 1;
        if($ticketTypeDetail->level_approve){     //Nếu tồn tại cấp quản lý duyệt
            $employee_id = $data['employee_id'];
            $batch = array();
            for($i; $i <= $ticketTypeDetail->level_approve; $i++){
                //Get manager of employee
                $profile = (new Employee())->getDetailById($employee_id);
                if(!$profile->manager_id){
                    break;
                }
                //Tạo process
                $batch[] = array(
                    'manager_id' => $profile->manager_id,
                    'ticket_id' => $ticketId,
                    'status' => $i == 1 ? 'active' : 'inactive',
                );
                $employee_id = $profile->manager_id;
            }
            if($batch){
                (new TicketProcess())->add_batch($batch);
            }
            
        }
        //Get ticket type to manager
        $arrManager = (new TicketType())->getTicketTypeToManager((int)$data['type_id'])->toArray();
        if($arrManager){     //Các quản lý là người duyệt trực tiếp
            $batch2 = array();
            foreach($arrManager as $manager){
                if(!$manager->manager_id){
                    break;
                }
                //Tạo process
                $batch2[] = array(
                    'manager_id' => $manager->manager_id,
                    'ticket_id' => $ticketId,
                    'status' => $i == 1 ? 'active' : 'inactive',
                );
            }
            if($batch2){
                (new TicketProcess())->add_batch($batch2);
            }
        }

        return true;
    }
}
