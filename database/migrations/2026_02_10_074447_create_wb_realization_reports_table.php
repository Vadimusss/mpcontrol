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
        Schema::create('wb_realization_reports', function (Blueprint $table) {
            $table->id();
            $table->string('cabinet', 64)->comment('shop_id');
            $table->timestamp('inserted_at')->nullable();
            $table->bigInteger('realizationreport_id');
            $table->date('date_from');
            $table->date('date_to');
            $table->date('create_dt')->nullable();
            $table->string('currency_name', 16)->nullable();
            $table->json('suppliercontract_code')->nullable();
            $table->bigInteger('rrd_id');
            $table->bigInteger('gi_id');
            $table->decimal('dlv_prc', 10, 4)->nullable();
            $table->date('fix_tariff_date_from')->nullable();
            $table->date('fix_tariff_date_to')->nullable();
            $table->string('subject_name', 255)->nullable();
            $table->bigInteger('nm_id');
            $table->string('brand_name', 255)->nullable();
            $table->string('sa_name', 255)->nullable();
            $table->string('ts_name', 128)->nullable();
            $table->string('barcode', 64)->nullable();
            $table->string('doc_type_name', 128)->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('retail_price', 12, 2)->default(0);
            $table->decimal('retail_amount', 12, 2)->default(0);
            $table->integer('sale_percent')->default(0);
            $table->decimal('commission_percent', 6, 3)->default(0);
            $table->string('office_name', 255)->nullable();
            $table->string('supplier_oper_name', 255)->nullable();
            $table->timestamp('order_dt')->nullable();
            $table->timestamp('sale_dt')->nullable();
            $table->date('rr_dt')->nullable();
            $table->bigInteger('shk_id');
            $table->decimal('retail_price_withdisc_rub', 12, 2)->default(0);
            $table->integer('delivery_amount')->default(0);
            $table->integer('return_amount')->default(0);
            $table->decimal('delivery_rub', 12, 2)->default(0);
            $table->string('gi_box_type_name', 128)->nullable();
            $table->decimal('product_discount_for_report', 6, 3)->default(0);
            $table->decimal('supplier_promo', 6, 3)->default(0);
            $table->decimal('ppvz_spp_prc', 6, 3)->default(0);
            $table->decimal('ppvz_kvw_prc_base', 6, 3)->default(0);
            $table->decimal('ppvz_kvw_prc', 6, 3)->default(0);
            $table->decimal('sup_rating_prc_up', 6, 3)->default(0);
            $table->decimal('is_kgvp_v2', 6, 3)->default(0);
            $table->decimal('ppvz_sales_commission', 14, 2)->default(0);
            $table->decimal('ppvz_for_pay', 14, 2)->default(0);
            $table->decimal('ppvz_reward', 14, 2)->default(0);
            $table->decimal('acquiring_fee', 14, 2)->default(0);
            $table->decimal('acquiring_percent', 6, 3)->default(0);
            $table->string('payment_processing', 64)->nullable();
            $table->string('acquiring_bank', 128)->nullable();
            $table->decimal('ppvz_vw', 14, 2)->default(0);
            $table->decimal('ppvz_vw_nds', 14, 2)->default(0);
            $table->string('ppvz_office_name', 255)->nullable();
            $table->integer('ppvz_office_id')->nullable();
            $table->integer('ppvz_supplier_id')->nullable();
            $table->string('ppvz_supplier_name', 255)->nullable();
            $table->string('ppvz_inn', 32)->nullable();
            $table->string('declaration_number', 128)->nullable();
            $table->string('bonus_type_name', 255)->nullable();
            $table->bigInteger('sticker_id')->nullable();
            $table->string('site_country', 64)->nullable();
            $table->boolean('srv_dbs')->default(false);
            $table->decimal('penalty', 14, 2)->default(0);
            $table->decimal('additional_payment', 14, 2)->default(0);
            $table->decimal('rebill_logistic_cost', 14, 2)->default(0);
            $table->string('rebill_logistic_org', 255)->nullable();
            $table->decimal('storage_fee', 14, 2)->default(0);
            $table->decimal('deduction', 14, 2)->default(0);
            $table->decimal('acceptance', 14, 2)->default(0);
            $table->bigInteger('assembly_id')->nullable();
            $table->string('kiz', 255)->nullable();
            $table->string('srid', 64)->nullable();
            $table->smallInteger('report_type')->nullable();
            $table->boolean('is_legal_entity')->default(false);
            $table->string('trbx_id', 64)->nullable();
            $table->decimal('installment_cofinancing_amount', 14, 2)->default(0);
            $table->decimal('wibes_wb_discount_percent', 6, 3)->default(0);
            $table->decimal('cashback_amount', 14, 2)->default(0);
            $table->decimal('cashback_discount', 14, 2)->default(0);
            $table->decimal('cashback_commission_change', 14, 2)->default(0);
            $table->string('order_uid', 64)->nullable();
            $table->string('payment_schedule', 255)->nullable();
            $table->timestamps();

            // Индексы для оптимизации запросов
            $table->index(['date_from', 'nm_id', 'cabinet']);
            $table->index(['cabinet', 'date_from']);
            $table->index(['nm_id', 'date_from']);
            $table->index(['realizationreport_id']);
            $table->index(['rrd_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_realization_reports');
    }
};