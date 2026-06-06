<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconciliation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->date('reconciliation_date');
            $table->integer('kitchen_quantity')->default(0);
            $table->decimal('kitchen_amount', 12, 2)->default(0);
            $table->integer('sales_quantity')->default(0);
            $table->decimal('sales_amount', 12, 2)->default(0);
            $table->integer('paid_quantity')->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->integer('missing_items')->default(0);
            $table->decimal('missing_sales', 12, 2)->default(0);
            $table->decimal('pending_payments', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_logs');
    }
};
