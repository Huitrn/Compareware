<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('comparaciones', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('periferico1_id');
        $table->unsignedBigInteger('periferico2_id');
        $table->text('descripcion')->nullable();
        $table->timestamps();

        // Relaciones (opcional)
       // $table->foreign('periferico1_id')->references('id')->on('perifericos')->onDelete('cascade');
        //$table->foreign('periferico2_id')->references('id')->on('perifericos')->onDelete('cascade');
    });
}
};