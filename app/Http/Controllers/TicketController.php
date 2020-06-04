<?php

namespace App\Http\Controllers;

use App\Ticket;
use App\TicketType;
use App\TicketProcess;
use App\Employee;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request,Ticket $ticketModel)
    {
        $input = $request->only('employee_id','status','from_date','to_date');
        $arrTicket = $ticketModel->getLists($input);

        if ($arrTicket) {
            $arrTicketTypeId = $arrTicket->groupBy('type_id')->keys()->all();
            $ticketTypeModel = new TicketType;
            $arrTicketType = $ticketTypeModel->getArrDetailById($arrTicketTypeId)->groupBy('type_id');
            foreach ($arrTicket as $key => $ticket) {
                //var_dump($arrTicketType[$ticket->type_id]); die;
                $arrTicket[$key]->type_name = $arrTicketType[$ticket->type_id][0]->name;
            }
        }
        return response()->json($arrTicket);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request,Ticket $ticketModel, TicketType $ticketTypeModel)
    {
        $input = $request->only('employee_id','reason','type_id','data','location');
        // get type detail
        $validator = \Validator::make($input, [
            'type_id' => 'required',
            'data' => 'required'
        ]);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(["error" => 'validate', 'error_description'=>$validator->errors()->first()]);
        }
        $typeDetail = $ticketTypeModel->getDetailById((int)$input['type_id']);
        if (!$typeDetail) {
            return response()->json(["error" => 'validate', 'error_description'=>"Loại đơn này không tồn tại"]);
        }
        $template = json_decode($typeDetail->template,TRUE);
        $ticketId = $ticketModel->add($input);
        //Sau khi tạo ticket xong -> tạo process (nếu có)
        $i = 1;
        if($typeDetail->level_approve){     //Nếu tồn tại cấp quản lý duyệt
            $employee_id = $input['employee_id'];
            $batch = array();
            for($i; $i <= $typeDetail->level_approve; $i++){
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
        $arrManager = (new TicketType())->getTicketTypeToManager((int)$input['type_id'])->toArray();
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

        return response()->json(['ticket_id' => $ticketId]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request,Ticket $ticketModel)
    {
        $input = $request->only('employee_id','ticket_id');
        $result = $ticketModel->delete(['employee_id' => $input['employee_id'],'ticket_id' => $input['ticket_id']]);
        return response()->json(['result' => $result]);
    }
    /**
     * Detail the specified resource from storage.
     *
     * @param  \App\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request,Ticket $ticketModel)
    {
        $input = $request->only('employee_id','ticket_id');
        $ticketDetail = $ticketModel->getDetailById(['ticket_id' => $input['ticket_id'],'employee_id' => $input['employee_id']]);
        return response()->json($ticketDetail);
    }
}
