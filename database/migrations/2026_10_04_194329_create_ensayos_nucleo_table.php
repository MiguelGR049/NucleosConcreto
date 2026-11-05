<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Una hoja por fecha de ensayo (encabezado)
        Schema::create('hojas_ensayo', function (Blueprint $table) {
            $table->id('id_seg');

            $table->foreignId('fecha_ensayo_id')
                ->unique()
                ->constrained('fechas_ensayo', 'id_seg')
                ->cascadeOnDelete();

            $table->decimal('tmn', 7, 4)->nullable();
            $table->decimal('resistencia_proyecto', 9, 4)->nullable();

            $table->unsignedBigInteger('usuario_captura_id')->nullable()->index();
            $table->timestamps();
        });

        // Un renglón por núcleo (cada columna de la hoja)
        Schema::create('ensayos_nucleo', function (Blueprint $table) {
            $table->id('id_seg');

            // NUEVO: el núcleo nace en la recepción
            $table->foreignId('recepcion_id')
                ->constrained('recepciones', 'id_seg')
                ->cascadeOnDelete();

            // CAMBIO: nullable hasta que se asigne a una hoja (captura de propiedades)
            $table->foreignId('hoja_id')
                ->nullable()
                ->constrained('hojas_ensayo', 'id_seg')
                ->nullOnDelete();

            $table->unsignedTinyInteger('columna')->nullable();   // CAMBIO: nullable
            $table->string('no_nucleo', 20);
            $table->string('elemento', 100);
            $table->decimal('espesor', 8, 2)->nullable();
            $table->string('localizacion', 150);

            // CAMBIO: todas las mediciones nullable (se llenan en la captura)
            $table->decimal('masa_g', 12, 4)->nullable();
            $table->decimal('diametro_1_mm', 8, 4)->nullable();
            $table->decimal('diametro_2_mm', 8, 4)->nullable();
            $table->decimal('altura_1_sin_cabeceo_mm', 9, 4)->nullable();
            $table->decimal('altura_2_sin_cabeceo_mm', 9, 4)->nullable();
            $table->decimal('altura_1_con_cabeceo_mm', 9, 4)->nullable();
            $table->decimal('altura_2_con_cabeceo_mm', 9, 4)->nullable();
            $table->decimal('carga_kn', 10, 4)->nullable();
            $table->string('tipo_falla', 50)->nullable();

            $table->timestamps();

            $table->unique(['hoja_id', 'columna']);  // los NULL no chocan entre sí
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ensayos_nucleo');
        Schema::dropIfExists('hojas_ensayo');
    }
};
