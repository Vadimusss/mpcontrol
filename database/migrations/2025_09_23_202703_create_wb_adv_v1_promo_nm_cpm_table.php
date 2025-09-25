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
        Schema::create('wb_adv_v1_promo_nm_cpm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wb_adv_v1_promotion_adverts_id')->constrained('wb_adv_v1_promotion_adverts')->onDelete('cascade');
            $table->integer('nm')->nullable();
            $table->integer('cpm')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_adv_v1_promo_nm_cpm');
    }
};
