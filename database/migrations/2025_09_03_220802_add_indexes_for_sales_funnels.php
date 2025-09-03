<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales_funnels', function (Blueprint $table) {
            $table->index(['good_id']);
            $table->index(['date']);
            $table->index(['good_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_funnels', function (Blueprint $table) {
            $table->dropIndex(['good_id']);
            $table->dropIndex(['date']);
            $table->dropIndex(['good_id', 'date']);
        });
    }
};
