<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stok_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_material_id')->constrained('data_materials')->onDelete('cascade');
            $table->integer('stok');
            $table->integer('maksimum_stok');
            $table->enum('status', ['tidak overstock', 'overstock']);
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
        Schema::dropIfExists('stok_materials');
    }
};
