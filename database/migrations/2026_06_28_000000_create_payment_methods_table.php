<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'slug']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'm_pesa', 'card', 'bank_transfer'])->default('cash')->change();
        });
    }
};
