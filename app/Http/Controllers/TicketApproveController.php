<?php

namespace App\Http\Controllers;

use App\TicketApprove;
use App\Ticket;
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

        //$ticketApprove->getFolowId([])

        $result = $ticketApprove->accept(['ticket_id' => $input['ticket_id'],'manager_id' => $input['employee_id']]);
        return response()->json(['result' => $result]);
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

        $result = $ticketApprove->reject(['ticket_id' => $input['ticket_id'],'manager_id' => $input['employee_id']]);
        return response()->json(['result' => $result]);
    }
}
