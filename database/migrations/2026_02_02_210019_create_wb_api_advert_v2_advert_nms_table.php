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
        Schema::create('wb_api_advert_v2_advert_nms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wb_api_advert_v2_advert_id')->constrained('wb_api_advert_v2_adverts')->onDelete('cascade');
            $table->bigInteger('bids_kopecks_search');
            $table->bigInteger('bids_kopecks_recommendations');
            $table->bigInteger('nm_id');
            $table->bigInteger('subject_id');
            $table->string('subject_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_api_advert_v2_advert_nms');
    }
};
