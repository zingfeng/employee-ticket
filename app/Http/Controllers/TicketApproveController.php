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
        $arrTicket = $ticketApproveModel->getListProcessing();
        
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
    public function accept(Request $request,TicketApprove $ticketApprove)
    {

        $input = $request->only('employee_id','ticket_id');
        /////// GET DETAIL TICKET ///////
        $ticketModel = new Ticket;
        $ticketModel->getDetailById(['ticket_id' => $input['ticket_id']]);

        //Hòa Nguyễn - Get ticket process
        $processModel = new TicketProcess;
        $processDetail = $processModel->getDetail(['ticket_id' => $input['ticket_id'], 'manager_id' => $input['employee_id']]);
        //Không tồn tại ticket process hoặc stauts != 'inactive' thì return FALSE
        if(!$processDetail){
            return response()->json(['result' => FALSE, 'message' => 'Bạn không có quyền duyệt đơn này']);
        }else if(isset($processDetail->status) && $processDetail->status != 'active'){
            return response()->json(['result' => FALSE, 'message' => 'Bạn không thể thay đổi trạng thái của ticket process']);
        }
        //Đổi trạng thái của Ticket Process
        $processResult = $processModel->accept($processDetail->process_id);

        if($processResult){
            $processNxt = $processModel->getNxt($processDetail->process_id);
            //Nếu không còn process tiếp theo, tức tất cả đã được duyệt
            if(!$processNxt){
                $result = $ticketApprove->accept(['ticket_id' => $input['ticket_id'],'manager_id' => $input['employee_id']]); 
                return response()->json(['result' => $result]);
            }else{      //Process next chuyển trạng thái active
                $processModel->active($processNxt->process_id);
            }
        }

        // $result = $ticketApprove->accept(['ticket_id' => $input['ticket_id'],'manager_id' => $input['employee_id']]); 
        return response()->json(['result' => $processResult]);
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
        $ticketModel = new Ticket;
        $ticketModel->getDetailById(['ticket_id' => $input['ticket_id']]);

        //Hòa Nguyễn - Get ticket process
        $processModel = new TicketProcess;
        $processDetail = $processModel->getDetail(['ticket_id' => $input['ticket_id'], 'manager_id' => $input['employee_id']]);
        //Không tồn tại ticket process hoặc stauts != 'inactive' thì return FALSE
        if(!$processDetail){
            return response()->json(['result' => FALSE, 'message' => 'Bạn không có quyền từ chối đơn này']);
        }else if(isset($processDetail->status) && $processDetail->status != 'active'){
            return response()->json(['result' => FALSE, 'message' => 'Bạn không thể thay đổi trạng thái của ticket process']);
        }
        //Đổi trạng thái của Ticket Process
        $processResult = $processModel->reject($processDetail->process_id);
        if($processResult){         //Reject thành công thì reject luôn cả ticket
            $result = $ticketApprove->reject(['ticket_id' => $input['ticket_id'],'manager_id' => $input['employee_id']]);
            return response()->json(['result' => $result]);
        }
        return response()->json(['result' => $processResult]);
    }
}
