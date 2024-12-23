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
        Schema::create('wb_list_good_sizes', function (Blueprint $table) {
            $table->id();
            $table->integer('good_id');
            $table->integer('size_id');
            $table->integer('price');
            $table->integer('discounted_price');
            $table->integer('club_discounted_price');
            $table->string('tech_size_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_list_good_sizes');
    }
};
