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
        Schema::table('goods', function (Blueprint $table) {
            $table->foreignId('good_status_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null')
                  ->after('vendor_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->dropForeign(['good_status_id']);
            $table->dropColumn('good_status_id');
        });
    }
};
