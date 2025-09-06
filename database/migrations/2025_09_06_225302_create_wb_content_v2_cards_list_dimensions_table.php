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
        Schema::create('wb_cards_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wb_cards_list_id')->constrained('wb_content_v2_cards_lists')->onDelete('cascade');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('length')->nullable();
            $table->decimal('weight_brutto', 8, 2)->nullable();
            $table->boolean('is_valid')->default(false);
            $table->timestamps();
            
            $table->index('wb_cards_list_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_cards_dimensions');
    }
};
