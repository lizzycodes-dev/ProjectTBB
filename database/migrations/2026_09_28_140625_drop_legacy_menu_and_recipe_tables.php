<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove recipe tables that still reference the legacy menu_items table.
        Schema::dropIfExists('menu_option_recipe_adjustments');
        Schema::dropIfExists('recipe_items');

        // The POS and order records now use inventory_items.
        Schema::dropIfExists('menu_items');
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'This migration is irreversible because the legacy menu and recipe data was removed.'
        );
    }
};
