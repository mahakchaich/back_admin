<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class roles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('roles')->insert([
            [
                'type' => 'admin',
            ],
            [
                'type' => 'user',
            ],
            [
                'type' => 'partner',
            ],
            // Add more rows here
        ]);
    }
}
