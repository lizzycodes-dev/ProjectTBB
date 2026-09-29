<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE inventory_items
            MODIFY inventory_type
            ENUM('Prepped Food', 'Ingredient', 'Menu Item')
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE inventory_items
            MODIFY inventory_type
            ENUM('Prepped Food', 'Ingredient')
            NOT NULL
        ");
    }
};
