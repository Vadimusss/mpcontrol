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
        Schema::create('wb_adv_v3_fs_boosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wb_adv_v3_fullstats_wb_advert_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('avg_position', 8, 2);
            $table->date('date');
            $table->integer('nm_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wb_adv_v3_fs_boosters');
    }
};
