<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wb_adv_v0_auction_adverts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('bid_type')->nullable();
            $table->integer('advert_id');
            $table->integer('bids_recommendations');
            $table->integer('bids_search');
            $table->integer('nm_id');
            $table->integer('subject_id');
            $table->string('subject_name');
            $table->string('name');
            $table->string('payment_type');
            $table->boolean('placements_recommendations')->nullable();
            $table->boolean('placements_search')->nullable();
            $table->integer('status');
            $table->dateTime('created')->nullable();
            $table->dateTime('deleted')->nullable();
            $table->dateTime('started')->nullable();
            $table->dateTime('updated')->nullable();
            $table->timestamps();

            $table->index(['shop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wb_adv_v0_auction_adverts');
    }
};
