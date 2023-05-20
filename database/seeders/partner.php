<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class partner extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('partners')->insert([
            [
                'name' => 'partner',
                'email' => 'partner@gmail.com',
                'phone' => 53406288,
                'password' => Hash::make("aaaaaa"),
                'status' => 'ACTIVE',
                'description' => 'description of partner what to do and what its provide etc etc ... u can write here what ever you want about your company or store',
                'image' => 'saif-1973374126_1683287322.jpg',
                'category' => 'SUPERMARKET',
                'openingtime' => '08:00:00',
                'closingtime' => '20:00:00',
                'long' => 10.6369,
                'lat' =>  35.8247,
                'adress' =>  "sousse sude",
                'status' => 'ACTIVE',
                'role_id' => 3,
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'hamda',
                'email' => 'hamdamili7@gmail.com',
                'phone' => 54070844,
                'password' => Hash::make("aaaaaa"),
                'status' => 'ACTIVE',
                'description' => 'description of partner what to do and what its provide etc etc ... u can write here what ever you want about your company or store',
                'image' => '101166244_3848930005177840_4439555033557630976_n-138289135_1680314000.jpg',
                'category' => 'RESTAURANT',
                'openingtime' => '08:00:00',
                'closingtime' => '20:00:00',
                'long' => 10.5975,
                'lat' => 35.9017,
                'adress' =>  "hammam sousse",

                'role_id' => 3,
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                
            ],
            [
                'name' => 'maha123',
                'email' => 'mahakchaich11@gmail.com',
                'phone' => 53454258,
                'password' => Hash::make("aaaaaa"),
                'status' => 'INACTIVE',
                'description' => 'description of partner what to do and what its provide etc etc ... u can write here what ever you want about your company or store',
                'image' => 'saif-1973374126_1683287322.jpg',
                'category' => 'PASTRIES',
                'openingtime' => '08:00:00',
                'closingtime' => '20:00:00',
                'long' => 10.6381,
                'lat' =>    35.9017,
                'adress' =>  "sousse nord",

                'role_id' => 3,
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                
            ],
   
        ]);
    }
}
