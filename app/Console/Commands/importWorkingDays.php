<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class importWorkingDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:working_days';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        for ($year = 2020; $year < 2030; $year++) { 
            $oneDay = 86400;
            $strToTime = mktime(0, 0, 0, 1 , 1, $year);
            $dataImport = []; 
            for ($i=$strToTime; $i < $strToTime + $oneDay*366; $i += $oneDay) { 
                if (date('Y',$i) > $year) {
                    break;
                }
                $input = [];
                $input['date'] =  date('Ymd',$i);
                $WorkingWeekend = date("N",$i);
                $input['type'] = ($WorkingWeekend == 6 || $WorkingWeekend == 7) ? 'weekend' : 'working';
                $dataImport[] = $input;
            }
            \DB::table("employee_calendar")->insert($dataImport);
            echo 'Done '.$year."\n";
        }
        
    }
}
