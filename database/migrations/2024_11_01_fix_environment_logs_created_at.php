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
        Schema::table('environment_logs', function (Blueprint $table) {
            // Modificar created_at para que tenga un valor por defecto
            $table->timestamp('created_at')->default(now())->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('environment_logs', function (Blueprint $table) {
            // Revertir el cambio si es necesario
            $table->timestamp('created_at')->nullable(false)->change();
        });
    }
};