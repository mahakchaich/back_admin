<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(roles::class);
        $this->call(users::class);
        $this->call(partner::class);
        $this->call(boxs::class);
        $this->call(like_boxs::class);
        $this->call(like_partner::class);
        $this->call(commands::class);
        $this->call(box_command::class);
        // \App\Models\User::factory(10)->create();
    }
}
