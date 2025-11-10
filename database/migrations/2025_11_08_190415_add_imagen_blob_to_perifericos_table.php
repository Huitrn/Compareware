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
        Schema::table('perifericos', function (Blueprint $table) {
            // Agregar columna para almacenar imagen como BLOB (bytea en PostgreSQL)
            $table->binary('imagen_blob')->nullable()->after('galeria_imagenes');
            // Agregar columna para tipo MIME de la imagen
            $table->string('imagen_mime_type', 100)->nullable()->after('imagen_blob');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perifericos', function (Blueprint $table) {
            $table->dropColumn(['imagen_blob', 'imagen_mime_type']);
        });
    }
};
