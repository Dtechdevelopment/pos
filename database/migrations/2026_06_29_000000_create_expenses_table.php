<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['utilities', 'supplies', 'maintenance', 'rent', 'salaries', 'other']);
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'one_time']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
