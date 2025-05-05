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
            $table->decimal('aac_cpm', 10, 2)->default(0);
            $table->integer('aac_views')->default(0);
            $table->integer('aac_clicks')->default(0);
            $table->integer('aac_orders')->default(0);
            $table->decimal('aac_sum', 10, 2)->default(0);
            
            $table->decimal('auc_cpm', 10, 2)->default(0);
            $table->integer('auc_views')->default(0);
            $table->integer('auc_clicks')->default(0);
            $table->integer('auc_orders')->default(0);
            $table->decimal('auc_sum', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_funnels', function (Blueprint $table) {
            $table->dropColumn([
                'aac_cpm',
                'aac_views', 
                'aac_clicks',
                'aac_orders',
                'aac_sum',
                'auc_cpm',
                'auc_views',
                'auc_clicks',
                'auc_orders',
                'auc_sum'
            ]);
        });
    }
};
