<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('environment_logs', function (Blueprint $table) {
            // Modificar created_at para tener valor por defecto
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->change();
            
            // Agregar updated_at como nullable para compatibilidad con Laravel
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('environment_logs', function (Blueprint $table) {
            // Revertir los cambios
            $table->timestamp('created_at')->nullable(false)->change();
            $table->dropColumn('updated_at');
        });
    }
};
