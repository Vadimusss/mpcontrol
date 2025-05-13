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
        Schema::create('nsis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('good_id')->constrained()->cascadeOnDelete();
            $table->string('vendor_code')->nullable();
            $table->string('name')->nullable();
            $table->string('variant')->nullable();
            $table->string('fg_0')->nullable();
            $table->string('fg_1')->nullable();
            $table->string('fg_2')->nullable();
            $table->string('fg_3')->nullable();
            $table->string('set')->nullable();
            $table->string('series')->nullable();
            $table->string('status')->nullable();
            $table->decimal('cost_with_taxes', 10, 2)->nullable();
            $table->string('barcode')->nullable();
            $table->unsignedBigInteger('nm_id');
            $table->string('wb_object')->nullable();
            $table->decimal('wb_volume', 10, 2)->nullable();
            $table->string('wb_1')->nullable();
            $table->string('wb_2')->nullable();
            $table->timestamps();

            $table->index('nm_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nsis');
    }
};
