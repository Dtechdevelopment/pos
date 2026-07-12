<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('kitchen_orders');
        if (!in_array('is_addon', $columns)) {
            Schema::table('kitchen_orders', function (Blueprint $table) {
                $table->boolean('is_addon')->default(false)->after('notes');
            });
        }
    }

    public function down(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('kitchen_orders');
        if (in_array('is_addon', $columns)) {
            Schema::table('kitchen_orders', function (Blueprint $table) {
                $table->dropColumn('is_addon');
            });
        }
    }
};
