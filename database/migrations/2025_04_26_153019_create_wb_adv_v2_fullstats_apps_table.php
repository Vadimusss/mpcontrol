<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wb_adv_fs_apps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wb_adv_fs_day_id')
                ->constrained('wb_adv_fs_days', 'id')
                ->cascadeOnDelete();
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
            $table->integer('app_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wb_adv_fs_apps');
    }
};
