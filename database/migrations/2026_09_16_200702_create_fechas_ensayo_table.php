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
            $table->foreignId('recepcion_id')
                ->constrained('recepciones', 'id_seg')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('dia');
            $table->date('fecha_ensayo');
            $table->boolean('capturada')->default(false);
            $table->boolean('ensayada')->default(false);
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
