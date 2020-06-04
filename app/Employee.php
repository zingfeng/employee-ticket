<?php

namespace App;


class Employee
{
    public function getListById($arrEmployeeId) {
		return  \DB::table('employee')
			->select('employee_id','first_name','last_name','email')
            ->whereIn('employee_id', $arrEmployeeId)
            ->get();
	}
	public function getDetailById($employee_id) {
    	return  \DB::table('employee')
            ->select('employee_id','first_name','last_name','email','phone','manager_id')
            ->where(['employee_id' => $employee_id])
            ->first();
    }

}
