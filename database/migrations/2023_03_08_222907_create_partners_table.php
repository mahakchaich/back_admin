<?php

use App\Models\Roles;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('email')->unique();
            $table->integer('phone');
            $table->string('password');
            $table->string('image')->nullable();
            $table->integer('long')->nullable();
            $table->integer('lat')->nullable();
            $table->string('adress')->nullable();
            $table->enum('category', ['SUPERMARKET', 'BAKERY', 'PASTRIES', 'RESTAURANT', 'COFFEE SHOP', 'HOTEL', 'CATERER', 'LOCAL PRODUCERS']);
            $table->time('openingtime')->default('00:00:00');
            $table->time('closingtime')->default('00:00:00');
            $table->enum('status', ['PENDING', 'ACTIVE', 'INACTIVE'])->default('PENDING');
            $table->foreignId('role_id')->constrain("roles");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partners');
    }
}
