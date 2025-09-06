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
        Schema::create('wb_cards_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wb_cards_list_id')->constrained('wb_content_v2_cards_lists')->onDelete('cascade');
            $table->string('big')->nullable();
            $table->string('c246x328')->nullable();
            $table->string('c516x688')->nullable();
            $table->string('square')->nullable();
            $table->string('tm')->nullable();
            $table->timestamps();
            
            $table->index('wb_cards_list_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_cards_photos');
    }
};
