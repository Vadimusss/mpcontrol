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
        Schema::table('supplier_warehouses_stocks', function (Blueprint $table) {
            $table->integer('nm_id')->nullable()->after('warehouse_id');
            $table->string('vendor_code')->nullable()->after('nm_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_warehouses_stocks', function (Blueprint $table) {
            $table->dropColumn(['nm_id', 'vendor_code']);
        });
    }
};
