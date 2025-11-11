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
        Schema::create('comparisons_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('periferico1_id')->constrained('perifericos')->onDelete('cascade');
            $table->foreignId('periferico2_id')->constrained('perifericos')->onDelete('cascade');
            $table->json('comparison_data')->nullable(); // Guardar resultados de la comparación
            $table->string('session_id')->nullable(); // Para usuarios no autenticados
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
            
            // Índices para mejorar búsquedas
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparisons_history');
    }
};
