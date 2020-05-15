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
}
