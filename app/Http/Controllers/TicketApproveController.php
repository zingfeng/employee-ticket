<?php

namespace App\Http\Controllers;

use App\TicketApprove;
use App\Ticket;
use App\TicketProcess;
use App\TicketType;
use App\Employee;
use Illuminate\Http\Request;

class TicketApproveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request,TicketApprove $ticketApproveModel)
    {
        $input = $request->only('employee_id');
        $params = array(
            'status' => 'active',
            'manager_id' => $input['employee_id']
        );
        $arrTicket = $ticketApproveModel->getListTicketByManager($params);

        $arrTicketTypeId = $arrTicket->groupBy('type_id')->keys()->all();
        $arrEmployeeId = $arrTicket->groupBy('employee_id')->keys()->all();
        $ticketTypeModel = new TicketType;
        $arrTicketType = $ticketTypeModel->getArrDetailById($arrTicketTypeId)->groupBy('type_id');
        $employeeModel = new Employee;
        $arrEmployee = $employeeModel->getListById($arrEmployeeId)->groupBy('employee_id');

        foreach ($arrTicket as $key => $ticket) {
            $arrTicket[$key]->type_name = $arrTicketType[$ticket->type_id][0]->name;
            $arrTicket[$key]->employee_detail = $arrEmployee[$ticket->employee_id][0];
        }

        return response()->json($arrTicket);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request)
    {
        $input = $request->only('employee_id','ticket_id');
        $validator = \Validator::make($input, [
            'ticket_id' => 'required'
        ]);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(["error" => 'validate', 'error_description'=>$validator->errors()->first()]);
        }
        /////// GET DETAIL TICKET ///////
        $ticketModel = new Ticket;
        $ticketDetail = $ticketModel->getDetailById(['ticket_id' => $input['ticket_id']]);
        if(!$ticketDetail || $ticketDetail->status != 'open'){
            return response()->json(['error' => 'invalid_ticket', 'message' => 'Đơn không tồn tại hoặc đã được duyệt']);
        }
        //Hòa Nguyễn - Get ticket process
        $processModel = new TicketProcess;
        $processDetail = $processModel->getDetail(['ticket_id' => $input['ticket_id'], 'manager_id' => $input['employee_id']]);
        //Không tồn tại ticket process hoặc stauts != 'inactive' thì return FALSE
        if(!$processDetail || $processDetail->status == 'active'){
            return response()->json(['error' => 'invalid_process', 'message' => 'Duyệt đơn không tồn tại hoặc đã được duyệt']);
        }
        $result = \DB::transaction(function () use($processModel, $processDetail) {
            //Đổi trạng thái của Ticket Process
            $result = $processModel->accept($processDetail->process_id);
            if($result){
                $processNxt = $processModel->getNxt($processDetail->process_id);
                //Nếu không còn process tiếp theo, tức tất cả đã được duyệt
                if(!$processNxt){
                    $ticketApprove = new TicketApprove;
                    $status = $ticketApprove->accept(['ticket_id' => $processDetail->ticket_id]);
                    $result = ['manager_id' => $processNxt->manager_id,'event' => 'ticket_process_active'];
                }else{      
                    //Process next chuyển trạng thái active
                    $status = $processModel->active($processNxt->process_id);
                    $result = ['event' => 'ticket_approved'];
                }

            }
            return $result;
        }, 2);
        if (isset($result['event'])) {
            $eventData = array_merge(['employee_id' => $ticketDetail->employee_id,$result]);
            unset($data['event']);
            event(new \App\Events\BusEvent($result['event'],$eventData));
        }
        return response()->json(['result' => ($result) ? 1 : 0]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TicketApprove  $ticketApprove
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, TicketApprove $ticketApprove)
    {
        $input = $request->only('employee_id','ticket_id');
        $validator = \Validator::make($input, [
            'ticket_id' => 'required'
        ]);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(["error" => 'validate', 'error_description'=>$validator->errors()->first()]);
        }
        /////// GET DETAIL TICKET ///////
        $ticketModel = new Ticket;
        $ticketDetail = $ticketModel->getDetailById(['ticket_id' => $input['ticket_id']]);
        if(!$ticketDetail || $ticketDetail->status != 'open'){
            return response()->json(['error' => 'invalid_ticket', 'error_description' => 'Đơn không tồn tại hoặc đã được duyệt']);
        }
        //Hòa Nguyễn - Get ticket process
        $processModel = new TicketProcess;
        $processDetail = $processModel->getDetail(['ticket_id' => $input['ticket_id'], 'manager_id' => $input['employee_id']]);
        //Không tồn tại ticket process hoặc stauts != 'inactive' thì return FALSE
        if(!$processDetail || $processDetail->status == 'active'){
            return response()->json(['error' => 'invalid_process', 'error_description' => 'Duyệt đơn không tồn tại hoặc đã được duyệt']);
        }
        //Đổi trạng thái của Ticket Process
        $result = \DB::transaction(function () use($processModel, $processDetail) {
            $result = $processModel->reject($processDetail->process_id);
            if($result){//Reject thành công thì reject luôn cả ticket
                $result = $ticketApprove->reject(['ticket_id' => $input['ticket_id']]);
            }
            return $result;
        }, 2);
        if (isset($result)) {
            event(new \App\Events\BusEvent('ticket_rejected',['employee_id' => $ticketDetail->employee_id]));
        }
        
        return response()->json(['result' => $result]);
    }
}
