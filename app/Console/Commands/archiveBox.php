<?php

namespace App\Console\Commands;

use App\Models\archived_boxs;
use App\Models\Archived_Command;
use App\Models\archivedBoxCommand;
use App\Models\Box;
use App\Models\Command as ModelsCommand;
use Illuminate\Console\Command;
use Carbon\Carbon;

class archiveBox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'box:archive';

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
     
        $boxs = Box::all();
        $archivedBox = new archived_boxs();
        $archivedCommand = new Archived_Command();
        $archivedBoxCommand = new archivedBoxCommand();
        foreach ($boxs as $box) {
            //if box finished or expired
            if ($box->status === 'FINISHED' || $box->status === 'EXPIRED') {
                $boxCommands = $box->boxsCommand;  // get command that have the box 
                if (count($boxCommands) == 0) { // if box not in any command 
                    // archive box
                    try {
                        // code...
                        $archivedBox->create([
                            "id" => $box->id,
                            "title" => $box->title,
                            "description" => $box->description,
                            "oldprice" => $box->oldprice,
                            "newprice" => $box->newprice,
                            "startdate" => $box->startdate,
                            "enddate" => $box->enddate,
                            "quantity" => $box->quantity,
                            "remaining_quantity" => $box->remaining_quantity,
                            "image" => $box->image,
                            "category" => $box->category,
                            "status" => $box->status,
                            "partner_id" => $box->partner_id,
                        ]);
                        $box->delete();
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                } else { // if box inside commands
                    foreach ($boxCommands  as $boxCmnd) {
                        // get commands that contain the box_commands id and status not Pending
                        $commands = $boxCmnd->command->where("status", "=", "PENDING")->get();

                        // var_dump($commands);
                        if (count($commands) == 0) {
                            $commands = $boxCmnd->command->get();
                            // archive box_command
                            try {
                                $archivedBoxCommand->create([
                                    "id" => $boxCmnd->id,
                                    "box_id" => $boxCmnd->box_id,
                                    "command_id" => $boxCmnd->command_id,
                                    "quantity" => $boxCmnd->quantity,
                                ]);
                                $boxCmnd->delete();
                            } catch (\Throwable $th) {
                                throw $th;
                            }
                        };
                    };
                    foreach ($commands as $cmnd) {
                        // archive commands
                        try {
                            $archivedCommand->create([
                                "id" => $cmnd->id,
                                "user_id" => $cmnd->user_id,
                                "price" => $cmnd->price,
                                "status" => $cmnd->status,
                            ]);
                            $cmnd->delete();
                        } catch (\Throwable $th) {
                            throw $th;
                        }
                    };
                    // archive box
                    try {
                        $box->create([

                            "id" => $box->id,
                            "title" => $box->title,
                            "description" => $box->description,
                            "oldprice" => $box->oldprice,
                            "newprice" => $box->newprice,
                            "startdate" => $box->startdate,
                            "enddate" => $box->enddate,
                            "quantity" => $box->quantity,
                            "remaining_quantity" => $box->remaining_quantity,
                            "image" => $box->image,
                            "category" => $box->category,
                            "status" => $box->status,
                            "partner_id" => $box->partner_id,
                        ]);
                        $box->delete();
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                }
            }
        }
        return 0;
    }
}
