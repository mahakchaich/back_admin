<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->date('date_dispo');
            $table->integer('quantite');
            $table->string('image');
            $table->enum('categorie', ['Fruits_Légumes', 'Viande', 'Pâtisserie', 'Poisson', 'Produits_Laitiers', 'Plas_Préparés', 'Sucreries', 'Boissons', 'Végétarien']);
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
