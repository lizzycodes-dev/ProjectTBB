<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        // Stop rather than risk duplicating or overwriting an earlier transfer.
        if (Schema::hasTable('menu_item_transfer_map')) {
            throw new RuntimeException(
                'menu_item_transfer_map already exists. Check the database before continuing.'
            );
        }

        Schema::create('menu_item_transfer_map', function (Blueprint $table) {
            $table->unsignedBigInteger('old_menu_item_id')->primary();
            $table->unsignedBigInteger('new_inventory_item_id')->unique();
        });

        // Keep the original menu_items table. Only redirect the app's
        // order and option-group references to the new inventory records.
        Schema::table('menu_item_option_groups', function (Blueprint $table) {
            $table->dropForeign('menu_item_option_groups_menu_item_id_foreign');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign('order_items_menu_item_id_foreign');
        });

        $menuItems = DB::table('menu_items')->orderBy('id')->get();

        foreach ($menuItems as $menuItem) {
            // Give every menu product its own inventory row. The internal
            // name is unique; menu_name is the name shown to customers.
            $internalName = 'Menu Item #' . $menuItem->id . ' - ' . $menuItem->name;

            $newId = DB::table('inventory_items')->insertGetId([
                'unit_id' => null,
                'category_id' => $menuItem->category_id,
                'name' => $internalName,
                'menu_name' => $menuItem->name,
                'inventory_type' => 'Menu Item',
                'base_price' => $menuItem->base_price,
                'description' => $menuItem->description,
                'is_sellable' => true,
                'is_active' => $menuItem->is_active,
                'created_at' => $menuItem->created_at,
                'updated_at' => $menuItem->updated_at,
            ]);

            DB::table('menu_item_transfer_map')->insert([
                'old_menu_item_id' => $menuItem->id,
                'new_inventory_item_id' => $newId,
            ]);
        }

        // Redirect existing order history and option links using the map.
        foreach (DB::table('menu_item_transfer_map')->get() as $map) {
            DB::table('order_items')
                ->where('menu_item_id', $map->old_menu_item_id)
                ->update(['menu_item_id' => $map->new_inventory_item_id]);

            DB::table('menu_item_option_groups')
                ->where('menu_item_id', $map->old_menu_item_id)
                ->update(['menu_item_id' => $map->new_inventory_item_id]);
        }

        Schema::table('menu_item_option_groups', function (Blueprint $table) {
            $table->foreign('menu_item_id')
                ->references('id')
                ->on('inventory_items');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('menu_item_id')
                ->references('id')
                ->on('inventory_items');
        });
    }

    public function down(): void
    {
        throw new RuntimeException(
            'This data migration is not reversible automatically. Restore from your database backup if needed.'
        );
    }
};
