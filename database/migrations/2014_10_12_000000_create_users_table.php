<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->integer('phone');
            $table->string('password');
            
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->date('birthday')->nullable();
            $table->enum('sexe', ['FEMALE', 'MALE']);
            $table->foreignId('role_id')->constrain("roles");
            $table->timestamps();
        });

        // Add the constraint to enforce the minimum age of 18 years
        DB::statement('ALTER TABLE users ADD CONSTRAINT  CHECK (birthday <= DATE_SUB(NOW(), INTERVAL 18 YEAR))');

    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
