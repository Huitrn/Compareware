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
        Schema::create('environment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('environment', 20)->index(); // sandbox, staging, production
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->json('data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('created_at');
            
            // Índices para mejorar consultas
            $table->index(['environment', 'created_at']);
            $table->index(['action', 'environment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environment_logs');
    }
};