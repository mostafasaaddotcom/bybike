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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_for_birthday')->default(false)->after('is_available');
            $table->boolean('is_for_wedding')->default(false)->after('is_for_birthday');
            $table->boolean('is_indoor')->default(false)->after('is_for_wedding');
            $table->string('brand')->nullable()->after('is_indoor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_for_birthday', 'is_for_wedding', 'is_indoor', 'brand']);
        });
    }
};
