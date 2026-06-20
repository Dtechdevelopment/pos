<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','sent_to_kitchen','preparing','ready','picked_up','delivered','closed','cancelled') DEFAULT 'pending'");
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','pending','partial','paid','cancelled','void','refunded') DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','sent_to_kitchen','preparing','ready','delivered','closed') DEFAULT 'pending'");
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft','pending','paid','cancelled','void') DEFAULT 'draft'");
    }
};
