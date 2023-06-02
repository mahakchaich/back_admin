<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivedCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    
        Schema::create('archived__commands', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->foreignId('user_id')->constrained('users','id');
            $table->float('price')->default(0);
            $table->enum('status', ['PENDING', 'SUCCESS', 'CANCEL']);
            
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
        Schema::dropIfExists('archived__commands');
    }
}
