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
            $table->integer('aac_cpm_id')->nullable()->after('profit_with_ads');
            $table->integer('auc_cpm_id')->nullable()->after('aac_cpm_id');
            $table->integer('auc_cpc_id')->nullable()->after('auc_cpm_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_funnels', function (Blueprint $table) {
            $table->dropColumn(['aac_cpm_id', 'auc_cpm_id', 'auc_cpc_id']);
        });
    }
};
