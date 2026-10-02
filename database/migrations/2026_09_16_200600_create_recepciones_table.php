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
        Schema::create('recepciones', function (Blueprint $table) {
            $table->id('id_seg');
            $table->string('folio')->unique();
            $table->string('clave_obra');
            $table->date('fecha_rec');
            $table->unsignedTinyInteger('n_espec');
            $table->string('elemento'); // guardamos el nombre en mayúsculas, no FK
            $table->string('localizacion');
            $table->string('datos_proy')->nullable();
            $table->string('defectos')->nullable();
            $table->string('envia')->nullable();
            $table->string('recibe')->nullable();
            $table->date('fecha_muest');
            $table->string('observ')->nullable();
            $table->unsignedBigInteger('usuario_reg_id');
            $table->foreign('usuario_reg_id')->references('id')->on('usuarios');
            $table->timestamp('fecha_reg')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepciones');
    }
};
