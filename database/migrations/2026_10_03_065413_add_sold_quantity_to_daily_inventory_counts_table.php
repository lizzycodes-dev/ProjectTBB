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
        Schema::table('daily_inventory_counts', function (Blueprint $table) {
            $table->decimal('sold_quantity', 12, 3)
                ->default(0)
                ->after('beginning_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_inventory_counts', function (Blueprint $table) {
            $table->dropColumn('sold_quantity');
        });
    }
};
