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
        Schema::create('wb_list_goods', function (Blueprint $table) {
            $table->id();
            $table->integer('good_id');
            $table->integer('nm_id');
            $table->string('vendor_code');
            $table->string('currency_iso_code_4217');
            $table->integer('discount')->default(0);
            $table->integer('club_discount')->default(0);
            $table->boolean('editable_size_price')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_list_goods');
    }
};
