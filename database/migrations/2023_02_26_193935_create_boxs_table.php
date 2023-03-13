<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoxsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boxs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('oldprice');
            $table->decimal('newprice');
            $table->dateTime('startdate');
            $table->dateTime('enddate');
            $table->integer('quantity');
            $table->integer('remaining_quantity')->default(DB::raw('`quantity`'));
            $table->string('image');
            $table->enum('category', ['FRUITS AND VEGETABLES', 'MEAT', 'PASTRY', 'FISH', 'DAIRY PRODUCTS', 'PREPARED DISHES', 'SWEETS', 'DRINKS', 'VEGETARIAN']);
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED', 'FINISHED', 'EXPIRED'])->default('PENDING');
            $table->foreignId('partner_id')->constrain('partners');
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
        Schema::dropIfExists('boxs');
    }
}
