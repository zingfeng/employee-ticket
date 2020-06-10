<?php

namespace App;


class TicketType
{
    public function getDetailById($typeId){
    	return \DB::table('ticket_type')
            ->where(['type_id' => $typeId])
            ->first();
    }
    public function getLists($params = array()){
    	$objDb = \DB::table('ticket_type')
    		->select('name','type_id','category_id');
        if (isset($params['code'])) {
            $objDb->where('code',$params['code']);
        }
        return $objDb->get();
    }
    public function getArrDetailById($arrTicketTypeId) {
        return \DB::table('ticket_type')
            ->select('type_id','name','max_days','max_times')
            ->whereIn('type_id', $arrTicketTypeId)
            ->get();
    }
    public function getTicketTypeByHandle($handler){
        return \DB::table('ticket_type')
            ->where(['handler' => $handler])
            ->first();
    }
    public function getCategoryTree(){
        return \DB::table('ticket_category')
            ->get();
    }
    public function getTicketTypeToManager($type_id){
        return \DB::table('ticket_type_to_manager')
            ->where(['type_id' => $type_id])
            ->get();
    }
}
