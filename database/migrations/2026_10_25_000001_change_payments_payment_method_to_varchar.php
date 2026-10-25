<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('payments');
        if (in_array('payment_method', $columns)) {
            DB::statement("ALTER TABLE payments MODIFY payment_method VARCHAR(50) NOT NULL DEFAULT 'cash'");
        }
    }

    public function down(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('payments');
        if (in_array('payment_method', $columns)) {
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','card','mobile_money','bank_transfer','other') NOT NULL DEFAULT 'cash'");
        }
    }
};