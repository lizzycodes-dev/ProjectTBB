<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('categories');

            $table->decimal('base_price', 10, 2)
                ->nullable()
                ->after('inventory_type');

            $table->text('description')
                ->nullable()
                ->after('base_price');

            $table->boolean('is_sellable')
                ->default(false)
                ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'base_price',
                'description',
                'is_sellable',
            ]);
        });
    }
};
