<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fechas_ensayo', function (Blueprint $table) {
            $table->id('id_seg');
            $table->unsignedBigInteger('recepcion_id');
            $table->foreign('recepcion_id')->references('id_seg')->on('recepciones')->onDelete('cascade');
            $table->unsignedTinyInteger('dia'); // 1, 3, 7, 14 o 28
            $table->date('fecha_ensayo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechas_ensayo');
    }
};
