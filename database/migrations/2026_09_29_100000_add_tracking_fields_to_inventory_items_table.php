<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fields the new inventory screens need on each stock item:
     *  - sheet_group   : the section it appears under on the Daily Sheet
     *  - cost_per_unit : used for stock value and pre-filled on delivery receipts
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('sheet_group', 30)
                ->nullable()
                ->after('inventory_type');

            $table->decimal('cost_per_unit', 10, 2)
                ->default(0)
                ->after('sheet_group');
        });

        // Back-fill existing rows so a database that is already seeded keeps working.
        $groups = [
            'Powder' => [
                'Vanilla', 'Caramel', 'Dark Chocolate', 'Matcha', 'Graham', 'Crushed Oreo',
                'Cookies & Cream', 'Strawberry', 'Mango', 'Ube', 'Red Velvet', 'Lemon Ice Tea',
            ],
            'Sauce' => ['Sauce - Caramel', 'Sauce - Chocolate', 'Sauce - Condensed Milk'],
            'Puree' => ['Puree - Strawberry', 'Puree - Blueberry', 'Puree - Mango'],
        ];

        foreach ($groups as $group => $names) {
            DB::table('inventory_items')
                ->whereIn('name', $names)
                ->update(['sheet_group' => $group]);
        }

        DB::table('inventory_items')
            ->where('inventory_type', 'Prepped Food')
            ->update(['sheet_group' => 'Prepped Food']);
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['sheet_group', 'cost_per_unit']);
        });
    }
};
