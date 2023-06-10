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
                'description' => 'you can write here what ever you want about your company or store',
                'image' => 'saif-1973374126_1683287322.jpg',
                'category' => 'SUPERMARKET',
                'openingtime' => '08:00:00',
                'closingtime' => '20:00:00',
                // 'long' => 10.6369,
                // 'lat' =>  35.8247,
                // 'adress' =>  "sousse sude",
                'long' => 9.00001,
                'lat' =>  33.4667,
                'adress' =>  "Avenue Habib Bourguiba",
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
                'description' => 'you can write here what ever you want about your company or store',
                'image' => 'shedi-1973374126_1683287322.jpg',
                'category' => 'RESTAURANT',
                'openingtime' => '08:00:00',
                'closingtime' => '20:00:00',
                // 'long' => 10.5975,
                // 'lat' => 35.9017,
                // 'adress' =>  "hammam sousse",
                'long' => 8.9717,
                'lat' => 33.6804,
                'adress' =>  "Avenue des Martyrs",

                'role_id' => 3,
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                
            ],
            [
                'name' => 'ahmed123',
                'email' => 'ahmedahmed11@gmail.com',
                'phone' => 53454258,
                'password' => Hash::make("aaaaaa"),
                'status' => 'INACTIVE',
                'description' => 'you can write here what ever you want about your company or store',
                'image' => 'ahmed-2973374126_1683287322.jpg',
                'category' => 'PASTRIES',
                'openingtime' => '08:00:00',
                'closingtime' => '20:00:00',
                // 'long' => 10.6381,
                // 'lat' =>    35.9017,
                // 'adress' =>  "sousse nord",
                'long' =>  8.9723,
                'lat' =>    33.6892,
                'adress' =>  "Avenue Farhat Hached",

                'role_id' => 3,
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                
            ],
            [
                'name' => 'hamda123',
                'email' => 'hamdamili11@gmail.com',
                'phone' => 53454258,
                'password' => Hash::make("aaaaaa"),
                'status' => 'INACTIVE',
                'description' => 'you can write here what ever you want about your company or store',
                'image' => 'ahmed-1973374126_1683287322.jpg',
                'category' => 'PASTRIES',
                'openingtime' => '08:00:00',
                'closingtime' => '20:00:00',
                // 'long' => 10.6381,
                // 'lat' =>    35.9017,
                // 'adress' =>  "sousse nord",
                'long' => 9.0114,
                'lat' => 33.6400,
                'adress' =>  "Rue du Village",

                'role_id' => 3,
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                
            ],
   
        ]);
    }
}
