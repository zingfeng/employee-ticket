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
    public function getDetailById($params) {
    	return \DB::table('ticket')
            ->where(['ticket_id' => $params['ticket_id']])
            ->first();
    }
    public function accept($params) {
        //check manager

        //active 
        
        // \DB::table('ticket_process')
        //     ->insert([
        //         'ticket_id' => $params['ticket_id'],
        //         'manager_id' => $params['manager_id']
        //     ]);
        return \DB::table('ticket')
            ->where(['ticket_id' => $params['ticket_id'],'status' => 'open'])
            ->update(['status' => 'approved']);

    }
    public function getFolowId($params) {
        //get department 
        return \DB::table('ticket_type_flow')
            ->where(['type_id' => $params['type_id'],'manager_id' => $params['manager_id']])
            ->first();
    }
    public function reject($params) {
        //active 
        return \DB::table('ticket')
            ->where(['ticket_id' => $params['ticket_id'],'status' => 'open'])
            ->update(['status' => 'rejected']);
    } 
}
