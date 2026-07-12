<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('branches');
        if (!in_array('order_method', $columns)) {
            Schema::table('branches', function (Blueprint $table) {
                $table->string('order_method', 10)->default('digital')->after('status');
            });
        }
    }

    public function down(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('branches');
        if (in_array('order_method', $columns)) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('order_method');
            });
        }
    }
};
