<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 1000)->nullable();
            $table->string('AP_paterno', 20);
            $table->string('AP_materno', 20);
            $table->string('area', 200);
            $table->string('usuario', 30);
            $table->string('password', 300);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};