<?php

namespace App\Jobs;

class BusJob extends Job
{
    protected $key;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $listener = new $this->key;
        return $listener->handle($this->data);
    }
}
