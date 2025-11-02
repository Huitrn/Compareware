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
            // Modificar la columna created_at para que tenga un valor por defecto
            $table->timestamp('created_at')->default(now())->change();
            
            // Agregar updated_at para completar los timestamps de Laravel
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('environment_logs', function (Blueprint $table) {
            $table->dropColumn('updated_at');
            // Revertir created_at a su estado original
            $table->timestamp('created_at')->nullable(false)->change();
        });
    }
};