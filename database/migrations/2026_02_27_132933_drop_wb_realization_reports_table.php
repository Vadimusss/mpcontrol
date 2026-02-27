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
        Schema::dropIfExists('wb_realization_reports');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('wb_realization_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cabinet');
            $table->timestamp('inserted_at');
            $table->unsignedBigInteger('realizationreport_id');
            $table->date('date_from');
            $table->date('date_to');
            $table->dateTime('create_dt');
            $table->string('currency_name')->nullable();
            $table->string('suppliercontract_code')->nullable();
            $table->unsignedBigInteger('rrd_id');
            $table->unsignedBigInteger('gi_id');
            $table->decimal('dlv_prc', 10, 2)->nullable();
            $table->date('fix_tariff_date_from')->nullable();
            $table->date('fix_tariff_date_to')->nullable();
            $table->string('subject_name')->nullable();
            $table->unsignedBigInteger('nm_id');
            $table->string('brand_name')->nullable();
            $table->string('sa_name')->nullable();
            $table->string('ts_name')->nullable();
            $table->string('barcode')->nullable();
            $table->string('doc_type_name')->nullable();
            $table->integer('quantity');
            $table->decimal('retail_price', 10, 2);
            $table->decimal('retail_amount', 10, 2);
            $table->decimal('sale_percent', 5, 2)->nullable();
            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->string('office_name')->nullable();
            $table->string('supplier_oper_name')->nullable();
            $table->dateTime('order_dt');
            $table->dateTime('sale_dt');
            $table->dateTime('rr_dt');
            $table->string('shk_id')->nullable();
            $table->decimal('retail_price_withdisc_rub', 10, 2)->nullable();
            $table->decimal('delivery_amount', 10, 2)->nullable();
            $table->decimal('return_amount', 10, 2)->nullable();
            $table->decimal('delivery_rub', 10, 2)->nullable();
            $table->string('gi_box_type_name')->nullable();
            $table->decimal('product_discount_for_report', 10, 2)->nullable();
            $table->decimal('supplier_promo', 10, 2)->nullable();
            $table->decimal('ppvz_spp_prc', 10, 2)->nullable();
            $table->decimal('ppvz_kvw_prc_base', 10, 2)->nullable();
            $table->decimal('ppvz_kvw_prc', 10, 2)->nullable();
            $table->decimal('sup_rating_prc_up', 10, 2)->nullable();
            $table->boolean('is_kgvp_v2')->default(false);
            $table->decimal('ppvz_sales_commission', 10, 2)->nullable();
            $table->decimal('ppvz_for_pay', 10, 2)->nullable();
            $table->decimal('ppvz_reward', 10, 2)->nullable();
            $table->decimal('acquiring_fee', 10, 2)->nullable();
            $table->decimal('acquiring_percent', 5, 2)->nullable();
            $table->decimal('payment_processing', 10, 2)->nullable();
            $table->string('acquiring_bank')->nullable();
            $table->decimal('ppvz_vw', 10, 2)->nullable();
            $table->decimal('ppvz_vw_nds', 10, 2)->nullable();
            $table->string('ppvz_office_name')->nullable();
            $table->string('ppvz_office_id')->nullable();
            $table->string('ppvz_supplier_id')->nullable();
            $table->string('ppvz_supplier_name')->nullable();
            $table->string('ppvz_inn')->nullable();
            $table->string('declaration_number')->nullable();
            $table->string('bonus_type_name')->nullable();
            $table->string('sticker_id')->nullable();
            $table->string('site_country')->nullable();
            $table->decimal('srv_dbs', 10, 2)->nullable();
            $table->decimal('penalty', 10, 2)->nullable();
            $table->decimal('additional_payment', 10, 2)->nullable();
            $table->decimal('rebill_logistic_cost', 10, 2)->nullable();
            $table->string('rebill_logistic_org')->nullable();
            $table->decimal('storage_fee', 10, 2)->nullable();
            $table->decimal('deduction', 10, 2)->nullable();
            $table->decimal('acceptance', 10, 2)->nullable();
            $table->string('assembly_id')->nullable();
            $table->string('kiz')->nullable();
            $table->string('srid')->nullable();
            $table->string('report_type')->nullable();
            $table->boolean('is_legal_entity')->default(false);
            $table->string('trbx_id')->nullable();
            $table->decimal('installment_cofinancing_amount', 10, 2)->nullable();
            $table->decimal('wibes_wb_discount_percent', 5, 2)->nullable();
            $table->decimal('cashback_amount', 10, 2)->nullable();
            $table->decimal('cashback_discount', 10, 2)->nullable();
            $table->decimal('cashback_commission_change', 10, 2)->nullable();
            $table->string('order_uid')->nullable();
            $table->string('payment_schedule')->nullable();
            $table->timestamps();
        });
    }
};
