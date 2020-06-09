<?php

namespace App;


class TicketApprove
{
    public function getListProcessing($params = array()){
        $params = array_merge(['status' => 'open'],$params);
    	$objectDb =  \DB::table('ticket');
        if (isset($params['status'])) {
        	$objectDb->where('status',$params['status']);
        }
        return $objectDb->get();
    }
    public function getListTicketByManager($params = array()){
        $params = array_merge(['status' => 'open'],$params);
        $objectDb =  \DB::table('ticket');
        if (isset($params['status'])) {
            $objectDb->where('ticket_process.status',$params['status']);
        }
        if (isset($params['manager_id'])) {
            $objectDb->where('ticket_process.manager_id',$params['manager_id']);
        }
        $objectDb->join('ticket_process','ticket_process.ticket_id','=','ticket.ticket_id');
        return $objectDb->get();
    }
    public function getDetailById($params) {
    	return \DB::table('ticket')
            ->where(['ticket_id' => $params['ticket_id']])
            ->first();
    }
    public function accept($params) {
        return \DB::table('ticket')
            ->where(['ticket_id' => $params['ticket_id']])
            ->update(['status' => 'approved']);
    }
    public function reject($params) {
        //active
        return \DB::table('ticket')
            ->where(['ticket_id' => $params['ticket_id']])
            ->update(['status' => 'rejected']);
    }
}
