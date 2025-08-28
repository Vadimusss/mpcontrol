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
            $table->string('imt_name')->nullable()->change();
            $table->string('vendor_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wb_nm_report_detail_histories', function (Blueprint $table) {
            $table->string('imt_name')->nullable(false)->change();
            $table->string('vendor_code')->nullable(false)->change();
        });
    }
};
