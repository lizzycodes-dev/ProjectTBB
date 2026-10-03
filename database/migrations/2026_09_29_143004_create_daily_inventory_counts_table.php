<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_inventory_counts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            $table->date('stock_date');

            // The opening quantity for this item on this date.
            $table->decimal('beginning_quantity', 12, 3)->default(0);

            // The physical count entered at end of day; left null until counted.
            $table->decimal('ending_quantity', 12, 3)->nullable();

            // Link to the stock-in row that records the beginning quantity.
            $table->foreignId('beginning_stock_in_id')
                ->nullable()
                ->constrained('stock_ins')
                ->nullOnDelete();

            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(
                ['inventory_item_id', 'stock_date'],
                'daily_inventory_item_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_inventory_counts');
    }
};
