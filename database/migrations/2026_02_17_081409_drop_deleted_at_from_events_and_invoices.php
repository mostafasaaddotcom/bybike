<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Permanently delete any soft-deleted invoice items belonging to soft-deleted invoices
        DB::table('invoice_items')
            ->whereIn('invoice_id', DB::table('invoices')->whereNotNull('deleted_at')->pluck('id'))
            ->delete();

        // Permanently delete any soft-deleted invoices
        DB::table('invoices')->whereNotNull('deleted_at')->delete();

        // Permanently delete any invoice items belonging to soft-deleted events
        DB::table('invoice_items')
            ->whereIn('invoice_id', DB::table('invoices')->whereIn('event_id', DB::table('events')->whereNotNull('deleted_at')->pluck('id'))->pluck('id'))
            ->delete();

        // Permanently delete any invoices belonging to soft-deleted events
        DB::table('invoices')
            ->whereIn('event_id', DB::table('events')->whereNotNull('deleted_at')->pluck('id'))
            ->delete();

        // Permanently delete any soft-deleted events
        DB::table('events')->whereNotNull('deleted_at')->delete();

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
