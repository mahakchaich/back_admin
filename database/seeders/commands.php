<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class commands extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('commands')->insert([
            [
                'user_id' => 2,
                'price' => 10,
                'status' => "PENDING",
                'created_at' => "2023-05-12 13:51:46",
                'updated_at' =>"2023-05-12 14:47:08",
            ],
            [
                'user_id' => 2,
                'price' => 10,
                'status' => "PENDING",
                'created_at' => "2023-05-12 13:51:46",
                'updated_at' =>"2023-05-12 14:47:08",
            ],
            [
                'user_id' => 2,
                'price' => 10,
                'status' => "PENDING",
                'created_at' => "2023-05-12 13:51:46",
                'updated_at' =>"2023-05-12 14:47:08",
            ],
            
        ]); 
       }
}
