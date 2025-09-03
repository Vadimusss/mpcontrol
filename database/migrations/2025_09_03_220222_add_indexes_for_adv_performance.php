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
        Schema::table('wb_adv_fs_products', function (Blueprint $table) {
            $table->index(['date']);
            $table->index(['wb_adv_fs_app_id']);
            $table->index(['date', 'wb_adv_fs_app_id']);
        });

        Schema::table('wb_adv_fs_apps', function (Blueprint $table) {
            $table->index(['wb_adv_fs_day_id']);
        });

        Schema::table('wb_adv_fs_days', function (Blueprint $table) {
            $table->index(['wb_adv_v2_fullstats_wb_advert_id']);
        });

        Schema::table('wb_adv_v2_fullstats_wb_adverts', function (Blueprint $table) {
            $table->index(['advert_id']);
        });

        Schema::table('wb_adv_v1_promotion_counts', function (Blueprint $table) {
            $table->index(['advert_id']);
            $table->index(['shop_id']);
            $table->index(['type']);
            $table->index(['shop_id', 'type']);
            $table->index(['advert_id', 'shop_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wb_adv_fs_products', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['wb_adv_fs_app_id']);
            $table->dropIndex(['date', 'wb_adv_fs_app_id']);
        });

        Schema::table('wb_adv_fs_apps', function (Blueprint $table) {
            $table->dropIndex(['wb_adv_fs_day_id']);
        });

        Schema::table('wb_adv_fs_days', function (Blueprint $table) {
            $table->dropIndex(['wb_adv_v2_fullstats_wb_advert_id']);
        });

        Schema::table('wb_adv_v2_fullstats_wb_adverts', function (Blueprint $table) {
            $table->dropIndex(['advert_id']);
        });

        Schema::table('wb_adv_v1_promotion_counts', function (Blueprint $table) {
            $table->dropIndex(['advert_id']);
            $table->dropIndex(['shop_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['shop_id', 'type']);
            $table->dropIndex(['advert_id', 'shop_id', 'type']);
        });
    }
};
