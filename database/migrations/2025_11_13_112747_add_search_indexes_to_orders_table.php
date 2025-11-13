<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add search indexes to orders table for performance optimization.
 *
 * This migration adds indexes to columns frequently used in search
 * queries, significantly improving query performance (CRITICAL FIX #5).
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add index for customer_name search
            $table->index('customer_name');

            // Add index for customer_email search
            $table->index('customer_email');

            // Drop existing status index and create composite index for status filtering with sorting
            $table->dropIndex(['status']);
            $table->index(['status', 'ordered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop indexes in reverse order
            $table->dropIndex(['status', 'ordered_at']);
            $table->dropIndex(['customer_email']);
            $table->dropIndex(['customer_name']);

            // Recreate the original status index
            $table->index('status');
        });
    }
};
