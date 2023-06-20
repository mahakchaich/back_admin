<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class users extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            [
                'email' => 'admin@gmail.com',
                'name' => 'admin',
                'password' => Hash::make("aaaaaa"),
                'phone' => 53406288,
                'role_id' => 1,
                'status' => 'ACTIVE',
                "birthday"=>"2005-06-19",
                "sexe"=>"MALE",
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                
            ],
            [
                'email' => 'user@gmail.com',
                'name' => 'user',
                'phone' => 53406288,
                'password' => Hash::make("aaaaaa"),
                'role_id' => 2,
                'status' => 'ACTIVE',
                "birthday"=>"2005-06-19",
                "sexe"=>"FEMALE",
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                
            ],
            [
                'email' => 'ahmedmili76@gmail.com',
                'name' => 'ahmed',
                'phone' => 53406288,
                'password' => Hash::make("aaaaaa"),
                'role_id' => 2,
                'status' => 'ACTIVE',
                "birthday"=>"2005-06-19",
                "sexe"=>"MALE",
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                
            ],
            [
                'email' => 'mahakchaich@gmail.com',
                'name' => 'maha',
                'phone' => 53454258,
                'password' => Hash::make("aaaaaa"),
                'role_id' => 2,
                'status' => 'INACTIVE',
                "birthday"=>"2005-06-19",
                "sexe"=>"FEMALE",
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                
            ],
   
        ]);
    }
}
