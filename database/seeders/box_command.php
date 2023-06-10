<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class box_command extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('box_command')->insert([
            [
                'box_id' => 1,
                'command_id' => 1,
                'quantity' => 1,
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'box_id' => 1,
                'command_id' => 2,
                'quantity' => 1,
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'box_id' => 1,
                'command_id' => 3,
                'quantity' => 1,
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],   
        ]); 
       }
}
