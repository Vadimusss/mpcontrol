<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wb_adv_v2_fullstats_wb_adverts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id');
            $table->integer('views');
            $table->integer('clicks');
            $table->decimal('ctr', 8, 2);
            $table->decimal('cpc', 8, 2);
            $table->decimal('sum', 12, 2);
            $table->integer('atbs');
            $table->integer('orders');
            $table->decimal('cr', 8, 2);
            $table->integer('shks');
            $table->decimal('sum_price', 12, 2);
            $table->date('date');
            $table->integer('advert_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wb_adv_v2_fullstats_wb_adverts');
    }
};
