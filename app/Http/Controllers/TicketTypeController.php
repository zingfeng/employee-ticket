<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TicketType;
use App\Ticket;

class TicketTypeController extends Controller
{
    /**
     * Detail the specified resource from storage.
     *
     * @param  \App\TicketType  $ticket
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request,TicketType $ticketTypeModel)
    {
        $input = $request->only('employee_id','type_id');
        $ticketTypeDetail = $ticketTypeModel->getDetailById(['type_id' => $input['type_id']]);
        if (!$ticketTypeDetail) {
            return response()->json(['error' => 'validate','error_description' => 'Không tồn tại']);
        }
        $ticketTypeDetail->template = json_decode(preg_replace('/\s+/', ' ', trim($ticketTypeDetail->template)));
        $ticketModel = new Ticket;
        $sumTicket = $ticketModel->sumTicket(['employee_id' => $input['employee_id']]);
        $ticketTypeDetail->count_ticket = $sumTicket;

        return response()->json($ticketTypeDetail);
    }
    /**
     * Detail the specified resource from storage.
     *
     * @param  \App\TicketType  $ticket
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request,TicketType $ticketTypeModel)
    {
        $arrCategory = $ticketTypeModel->getCategoryTree();
        foreach ($arrCategory as $key => $category) {
            $arrMap[$category->category_id] = $key;
        }
        $arrTicketType = $ticketTypeModel->getLists();
        foreach ($arrTicketType as $key => $ticketType) {
            $arrCategory[$arrMap[$ticketType->category_id]]->ticket_type[] = $ticketType;
        }
        return response()->json($arrCategory);
    }
}
