<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HIGH-03 FIX: Add unique index to prevent duplicate transactions
 * 
 * This prevents race condition double-crediting at the database level
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Unique composite index on (source, reference_id)
            // Chỉ apply cho records có reference_id không null
            $table->unique(['source', 'reference_id'], 'wallet_tx_source_ref_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique('wallet_tx_source_ref_unique');
        });
    }
};
