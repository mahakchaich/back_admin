<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommandesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */


    public function up()
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->date('date_cmd')->default(now()->toDateString());;
            $table->time('heure_cmd')->default(now()->toTimeString());
            $table->unsignedBigInteger('user_id');
            $table->float('total_prix');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });


        //Pour stocker les details de chaque panier commander
        Schema::create('commande_paniers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commande_id');
            $table->unsignedBigInteger('panier_id');
            $table->integer('quantite');
            $table->float('prix');
            $table->timestamps();

            $table->foreign('commande_id')->references('id')->on('commandes');
            $table->foreign('panier_id')->references('id')->on('paniers');
        });
    }




    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('commandes');
    }
}
