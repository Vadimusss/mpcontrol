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
        Schema::create('wb_api_advert_v2_adverts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->integer('advert_id');
            $table->string('bid_type');
            $table->integer('status');
            $table->string('settings_name');
            $table->string('settings_payment_type');
            $table->boolean('placements_search');
            $table->boolean('placements_recommendations');
            $table->dateTime('timestamps_created')->nullable();
            $table->dateTime('timestamps_updated')->nullable();
            $table->dateTime('timestamps_started')->nullable();
            $table->dateTime('timestamps_deleted')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_api_advert_v2_adverts');
    }
};
