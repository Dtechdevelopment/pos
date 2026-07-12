<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('expenses');
        if (in_array('category', $columns)) {
            DB::statement("ALTER TABLE expenses MODIFY COLUMN category VARCHAR(100) NOT NULL");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE expenses MODIFY COLUMN category ENUM('utilities','supplies','maintenance','rent','salaries','other') NOT NULL");
    }
};
