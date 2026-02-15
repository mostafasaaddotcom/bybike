<?php

use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Event::whereNotNull('public_invoice_token')->each(function (Event $event) {
            $event->update(['public_invoice_token' => Str::random(16)]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('public_invoice_token', 16)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('public_invoice_token', 64)->nullable()->change();
        });
    }
};
