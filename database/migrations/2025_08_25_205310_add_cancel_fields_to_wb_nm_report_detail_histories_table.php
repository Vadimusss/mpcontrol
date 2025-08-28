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
        Schema::table('wb_nm_report_detail_histories', function (Blueprint $table) {
            $table->integer('cancel_count')->default(0)->after('buyouts_sum_rub');
            $table->integer('cancel_sum_rub')->default(0)->after('cancel_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wb_nm_report_detail_histories', function (Blueprint $table) {
            $table->dropColumn(['cancel_count', 'cancel_sum_rub']);
        });
    }
};
