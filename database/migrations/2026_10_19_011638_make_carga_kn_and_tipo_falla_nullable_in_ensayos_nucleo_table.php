
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ensayos_nucleo', function (Blueprint $table) {
            $table->decimal('carga_kn', 10, 4)->nullable()->change();
            $table->string('tipo_falla', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ensayos_nucleo', function (Blueprint $table) {
            $table->decimal('carga_kn', 10, 4)->nullable(false)->change();
            $table->string('tipo_falla', 50)->nullable(false)->change();
        });
    }
};
