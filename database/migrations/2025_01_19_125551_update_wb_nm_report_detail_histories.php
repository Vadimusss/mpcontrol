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
            $table->index('good_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wb_nm_report_detail_histories', function (Blueprint $table) {
            $table->dropIndex(['good_id']);
        });
    }
};
