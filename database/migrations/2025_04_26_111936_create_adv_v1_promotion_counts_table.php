<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wb_adv_v1_promotion_counts', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->integer('type');
            $table->integer('status');
            $table->integer('advert_id');
            $table->dateTime('change_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wb_adv_v1_promotion_counts');
    }
};
