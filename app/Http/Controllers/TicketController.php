<?php

namespace App\Http\Controllers;

use App\Ticket;
use App\TicketType;
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
        $input = $request->only('employee_id','note','type_id','data','location');
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
