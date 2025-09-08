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
        Schema::create('wb_content_v2_cards_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->integer('nm_id');
            $table->integer('imt_id')->nullable();
            $table->uuid('nm_uuid')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_name')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('brand')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('need_kiz')->default(false);
            $table->string('video')->nullable();
            $table->boolean('wholesale_enabled')->default(false);
            $table->integer('wholesale_quantum')->nullable();
            $table->integer('length')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->decimal('weight_brutto', 8, 2)->nullable();
            $table->boolean('dimensions_is_valid')->default(false);
            $table->timestamp('created_at_api')->nullable();
            $table->timestamp('updated_at_api')->nullable();
            $table->timestamps();

            $table->index(['shop_id']);
            $table->index(['nm_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_content_v2_cards_lists');
    }
};
