<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaniersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paniers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('ancien_prix');
            $table->decimal('nouveau_prix');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->integer('quantite initial');
            $table->integer('quantite_restante')->default(0);
            $table->string('image');
            $table->enum('categorie', ['Fruits and vegetables', 'Meat', 'Pastry', 'Fish', 'Dairy products', 'Prepared dishes', 'Sweets', 'Drinks', 'Vegetarian']);
            $table->enum('statut', ['on hold', 'Accept', 'Refuse']);
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
        Schema::dropIfExists('paniers');
    }
}
