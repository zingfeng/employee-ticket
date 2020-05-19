<?php

namespace App;

class Ticket
{
	public function getLists($params = array()) {
		$objectDb =  \DB::table('ticket')
            ->where(['employee_id' => $params['employee_id']]);
        if (isset($params['status'])) {
        	$objectDb->where('status',$params['status']);
        }
        return $objectDb->get();
	}
    public function sumTicket($params = array()) {
        $objectDb =  \DB::table('ticket')
            ->select(\DB::raw('SUM(number_days) as num_days'),\DB::raw('count(*) as number_times'))
            ->where(['employee_id' => $params['employee_id']]);
            //->whereBetween('from_date',[date('Y-01-01'),date('Y-12-31')]);
        if (isset($params['status'])) {
            $objectDb->where('status',$params['status']);
        }
        if (isset($params['type_id'])) {
            $objectDb->where('type_id',$params['type_id']);
        }
        return $objectDb->first();
    }
	public function add($params) {
        $params = array_merge(['from_date' => date('Y-m-d')],$params);
		return \DB::table('ticket')->insertGetId([
    		'employee_id' => $params['employee_id'],
			'data'	=> (is_array($params['data'])) ? json_encode($params['data']) : $params['data'],
		    'type_id' => $params['type_id'],
		    'reason' => $params['reason'],
            'from_date' => $params['from_date']
		]);
	}
    public function delete($params = array()) {
    	return \DB::table('ticket')
            ->where(['ticket_id' => $params['ticket_id'],'employee_id' => $params['employee_id'],'status' => 0])
            ->delete();
    }
    public function getDetailById($params = array()) {
    	return \DB::table('ticket')
            ->where(['ticket_id' => $params['ticket_id']])
            ->first();
    }
}
