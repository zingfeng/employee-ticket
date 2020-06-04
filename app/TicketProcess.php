<?php
/**
*   Hòa Nguyễn
*/
namespace App;

class TicketProcess
{
    // Add ticket process
    public function add($params) {
        $params = array_merge(['created_time' => date('Y-m-d')], $params);
        $insertData = [
            'ticket_id' => $params['ticket_id'],
            'manager_id' => $params['manager_id'],
            'status' => $params['status'],
        ];
        if (isset($params['data'])) {
            $insertData['data'] = (is_array($params['data'])) ? json_encode($params['data']) : $params['data'];
        }
        return \DB::table('ticket_process')->insertGetId($insertData);
    }
    //Get detail
    public function getDetail($params = array()) {
    	return \DB::table('ticket_process')
            ->where(['ticket_id' => $params['ticket_id']])
            ->where(['manager_id' => $params['manager_id']])
            ->first();
    }
    public function getNxt($process_id) {
        return \DB::table('ticket_process')
            ->where('process_id', '>', $process_id)
            ->where(['status' => 'inactive'])
            ->first();
    }
    // Duyệt
    public function accept($process_id) {
        return \DB::table('ticket_process')
            ->where(['process_id' => $process_id, 'status' => 'active'])
            ->update(['status' => 'approved']);
    }
    // Từ chối
    public function reject($process_id) {
        return \DB::table('ticket_process')
            ->where(['process_id' => $process_id, 'status' => 'active'])
            ->update(['status' => 'rejected']);
    }
    // Chuyển trạng thái inactive -> active
    public function active($process_id) {
        return \DB::table('ticket_process')
            ->where(['process_id' => $process_id, 'status' => 'inactive'])
            ->update(['status' => 'active']);
    }
}
