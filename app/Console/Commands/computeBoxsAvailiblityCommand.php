<?php

namespace App\Console\Commands;

use App\Models\Box;
use Carbon\Carbon;
use Illuminate\Console\Command;

class computeBoxsAvailiblityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'box:availibilty';

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
     * @return int
     */
    public function handle()
    {
        // get list 
        // map
        //test date

        $boxs = Box::all();

        foreach ($boxs as $box) {
            if ($box->remaining_quantity === 0) {
                $box->status = 'FINISHED';
                $box->save();
            }

            $trigger = new Carbon($box->enddate);

            if ($trigger->lt(now())) {
                $box->status = 'EXPIRED';
                $box->save();
            }
        }
    }
}
