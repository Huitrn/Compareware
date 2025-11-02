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
        Schema::table('users', function (Blueprint $table) {
            // Cambiar columna role de CHAR(1) a VARCHAR(50)
            $table->string('role', 50)->default('user')->change();
        });
        
        // Actualizar usuarios existentes con rol 'a' a 'admin'
        DB::table('users')->where('role', 'a')->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir a CHAR si es necesario
            $table->char('role', 1)->default('u')->change();
        });
    }
};
