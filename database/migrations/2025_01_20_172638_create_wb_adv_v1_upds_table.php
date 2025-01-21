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
        Schema::create('wb_adv_v1_upds', function (Blueprint $table) {
            $table->id();
            $table->integer('good_id');
            $table->integer('upd_num')->default(0);
            $table->dateTime('upd_time')->default(null);
            $table->integer('upd_sum')->default(0);
            $table->integer('advert_id');
            $table->string('camp_name');
            $table->integer('advert_type');
            $table->string('payment_type');
            $table->integer('advert_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_adv_v1_upds');
    }
};
