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
        Schema::create('menu_option_recipe_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items');
            $table->foreignId('option_value_id')->constrained('option_values');
            $table->foreignId('inventory_item_id')->constrained('inventory_items');
            $table->decimal('quantity_adjustment', 12, 3);
            $table->timestamps();

            $table->unique(
                ['menu_item_id', 'option_value_id', 'inventory_item_id'],
                'mor_adjustment_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu__option__recipe__adjustments');
    }
};
