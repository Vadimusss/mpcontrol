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
        Schema::create('internal_nsis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('good_id')->constrained()->cascadeOnDelete();
            $table->text('cabinet')->nullable();
            $table->string('article_wb', 32)->nullable();
            $table->unsignedBigInteger('sku_wb')->nullable();
            $table->string('article_oz')->nullable();
            $table->unsignedBigInteger('sku_oz')->nullable();
            $table->string('product_name', 256)->nullable();
            $table->string('fg_0', 256)->nullable();
            $table->string('fg_1', 256)->nullable();
            $table->string('fg_2', 256)->nullable();
            $table->string('fg_3', 256)->nullable();
            $table->text('brand')->nullable();
            $table->string('subject', 256)->nullable();
            $table->text('category_oz')->nullable();
            $table->unsignedBigInteger('barcode')->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->timestamps();

            $table->index('sku_wb');
            $table->index('good_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_nsis');
    }
};
