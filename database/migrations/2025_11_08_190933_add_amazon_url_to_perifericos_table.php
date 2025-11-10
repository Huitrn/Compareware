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
            // URL de compra en Amazon
            $table->string('amazon_url', 1000)->nullable()->after('imagen_mime_type');
            // ASIN de Amazon (identificador único del producto)
            $table->string('amazon_asin', 20)->nullable()->after('amazon_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perifericos', function (Blueprint $table) {
            $table->dropColumn(['amazon_url', 'amazon_asin']);
        });
    }
};
