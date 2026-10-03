<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per counting day. status: open -> beginning_saved -> closed
        Schema::create('daily_inventory_sheets', function (Blueprint $table) {
            $table->id();
            $table->date('sheet_date');
            $table->string('status', 20)->default('open');
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('beginning_saved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'sheet_date']);
        });

        Schema::create('daily_inventory_sheet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_inventory_sheet_id')
                ->constrained('daily_inventory_sheets')
                ->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items');
            $table->decimal('beginning', 12, 3)->nullable();
            $table->decimal('ending', 12, 3)->nullable();
            // How much has already been deducted from stock for this row.
            $table->decimal('applied_out', 12, 3)->default(0);
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['daily_inventory_sheet_id', 'inventory_item_id'], 'sheet_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_inventory_sheet_entries');
        Schema::dropIfExists('daily_inventory_sheets');
    }
};
