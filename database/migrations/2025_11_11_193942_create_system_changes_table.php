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
        Schema::create('system_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action_type'); // create, update, delete
            $table->string('model_type'); // Periferico, User, Categoria, etc.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('description');
            $table->json('changes')->nullable(); // Detalles de los cambios
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('notified')->default(false); // Si ya se notificó
            $table->timestamps();
            
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_changes');
    }
};
