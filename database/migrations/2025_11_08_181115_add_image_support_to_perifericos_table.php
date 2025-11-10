<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega soporte completo de imágenes a la tabla perifericos:
     * - URL de imagen principal
     * - Galería de imágenes adicionales
     * - Alt text para accesibilidad
     * - Información de almacenamiento
     */
    public function up(): void
    {
        Schema::table('perifericos', function (Blueprint $table) {
            // URL de la imagen principal del producto
            $table->string('imagen_url', 500)->nullable()->after('precio');
            
            // Texto alternativo para accesibilidad (SEO y A11y)
            $table->string('imagen_alt', 255)->nullable()->after('imagen_url');
            
            // Galería de imágenes adicionales (JSON array)
            $table->json('galeria_imagenes')->nullable()->after('imagen_alt');
            
            // Path local de la imagen (para gestión interna)
            $table->string('imagen_path', 500)->nullable()->after('galeria_imagenes');
            
            // Thumbnail generado automáticamente
            $table->string('thumbnail_url', 500)->nullable()->after('imagen_path');
            
            // Tipo de fuente de la imagen (upload, url, api, scraping, etc.)
            $table->enum('imagen_source', ['upload', 'url', 'amazon', 'youtube', 'manual', 'default'])
                  ->default('manual')
                  ->after('thumbnail_url');
            
            // Índices para búsqueda y rendimiento
            $table->index('imagen_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perifericos', function (Blueprint $table) {
            // Eliminar índice primero
            $table->dropIndex(['imagen_url']);
            
            // Eliminar columnas
            $table->dropColumn([
                'imagen_url',
                'imagen_alt',
                'galeria_imagenes',
                'imagen_path',
                'thumbnail_url',
                'imagen_source'
            ]);
        });
    }
};
