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
    public function add(Request $request)
    {

        $input = $request->only('employee_id','reason','type_id','data','location');
        // get type detail
        $validator = \Validator::make($input, [
            'type_id' => 'required',
            'data' => 'required',
            'reason' => 'required'
        ]);
        if ($validator->fails()) {
            //pass validator errors as errors object for ajax response
            return response()->json(["error" => 'validate', 'error_description'=>$validator->errors()->first()]);
        }
        $ticketTypeModel = new TicketType;
        $typeDetail = $ticketTypeModel->getDetailById((int)$input['type_id']);
        if (!$typeDetail) {
            return response()->json(["error" => 'validate', 'error_description'=>"Loại đơn này không tồn tại"]);
        }
        //Sau khi tạo ticket xong -> tạo process (nếu có)
        $arrManagerId = [];
        if($typeDetail->level_approve){     //Nếu tồn tại cấp quản lý duyệt
            $employee_id = $input['employee_id'];
            for($i = 1; $i <= $typeDetail->level_approve; $i++){
                //Get manager of employee
                $profile = (new Employee())->getDetailById($employee_id);
                if(!$profile || !$profile->manager_id){
                    break;
                }
                $arrManagerId[] = $profile->manager_id;
                $employee_id = $profile->manager_id;
            }
        }
        //Get ticket type to manager
        $arrManager = $ticketTypeModel->getTicketTypeToManager((int)$input['type_id']);
        if($arrManager){     //Các quản lý là người duyệt trực tiếp
            $arrManagerId = array_merge($arrManagerId, $arrManager->only(['manager_id'])->values()->all());
        }
        if (!$arrManagerId) {
            return response()->json(["error" => 'validate', 'error_description'=>"Bạn không thể tạo đơn do chưa có quản lý"]);
        }
        ///////
        $ticketId = \DB::transaction(function () use($input,$arrManagerId) {
            $ticketModel = new Ticket;
            // tao ticket
            $ticketId = $ticketModel->add($input);
            $i = 1; $insertData = [];
            foreach ($arrManagerId as $key => $mid) {
                $insertData[] = array(
                    'manager_id' => $mid,
                    'ticket_id' => $ticketId,
                    'status' => $i == 1 ? 'active' : 'inactive',
                );
                $i ++;
            }
            (new TicketProcess)->add($insertData); 
            return $ticketId;
        }, 2);
        if ($ticketId) {
            event(new \App\Events\BusEvent('ticket_process_active',['employee_id' => $input['employee_id'],'ticket_id' => $ticketId,'manager_id' => $arrManagerId[0]]));
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
