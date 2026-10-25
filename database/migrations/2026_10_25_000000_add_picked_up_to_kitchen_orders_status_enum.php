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
        if (in_array('status', $columns)) {
            DB::statement("ALTER TABLE kitchen_orders MODIFY COLUMN status ENUM('pending','preparing','ready','picked_up','delivered','cancelled') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('kitchen_orders');
        if (in_array('status', $columns)) {
            DB::statement("ALTER TABLE kitchen_orders MODIFY COLUMN status ENUM('pending','preparing','ready','delivered','cancelled') DEFAULT 'pending'");
        }
    }
};