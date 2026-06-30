<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('icon_type')->default('none')->after('image');
            $table->string('icon_shape')->nullable()->after('icon_type');
            $table->string('icon_color')->nullable()->after('icon_shape');
            $table->string('icon_image')->nullable()->after('icon_color');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['icon_type', 'icon_shape', 'icon_color', 'icon_image']);
        });
    }
};
