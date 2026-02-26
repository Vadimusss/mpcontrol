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
        Schema::create('wb_expenses_by_order_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->date('order_date');
            $table->bigInteger('nm_id');
            $table->integer('orders_count')->default(0);
            $table->decimal('op_after_spp', 12, 2)->default(0);
            $table->decimal('logistics_total', 12, 2)->default(0);
            $table->decimal('amount_to_transfer', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['shop_id', 'order_date', 'nm_id']);

            $table->index(['shop_id', 'order_date']);

            $table->foreign('shop_id')
                ->references('id')
                ->on('shops')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_expenses_by_order_days');
    }
};
