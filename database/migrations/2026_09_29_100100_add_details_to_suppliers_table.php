<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('delivery_frequency')->nullable()->after('address');
            $table->date('last_delivery_date')->nullable()->after('delivery_frequency');
            $table->text('notes')->nullable()->after('last_delivery_date');
        });

        // Which inventory items each supplier provides.
        Schema::create('inventory_item_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['inventory_item_id', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_item_supplier');

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['delivery_frequency', 'last_delivery_date', 'notes']);
        });
    }
};
