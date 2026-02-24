<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shop_user', function (Blueprint $table) {
            $table->string('role')->default('manager')->after('user_id');
        });

        // Update existing records to have 'manager' role
       DB::table('shop_user')->whereNull('role')->update(['role' => 'manager']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_user', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
