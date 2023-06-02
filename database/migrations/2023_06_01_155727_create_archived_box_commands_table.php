<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivedBoxCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('archived_box_commands', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        // });
        Schema::create('archived_box_commands', function (Blueprint $table) {
            // $table->id();
            $table->unsignedBigInteger('id');
            // $table->foreignId('box_id')->constrained('archived_boxs','id');
            // $table->foreignId('command_id')->constrained('archived_commands','id');
            $table->unsignedBigInteger('box_id');
            $table->unsignedBigInteger('command_id');
            $table->unsignedInteger('quantity')->default(1);
            // $table->foreign('box_id')->references('id')->on('archived_boxs');
            // $table->foreign('command_id')->references('id')->on('archived_commands');

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
        Schema::dropIfExists('archived_box_commands');
    }
}
