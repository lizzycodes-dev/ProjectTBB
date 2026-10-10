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
        Schema::create('order_item_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_item_id')
                ->constrained('order_items')
                ->cascadeOnDelete();

            $table->foreignId('option_value_id')
                ->constrained('option_values')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['order_item_id', 'option_value_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_options');
    }
};
