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
        Schema::create('material_keluars', function (Blueprint $table) {
            $table->id();
            $table->date('waktu');
            $table->foreignId('data_material_id')->constrained('data_materials')->onDelete('cascade');
            $table->integer('jumlah');
            $table->string('satuan');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('material_keluars');
    }
};
