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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('voucher_number')->nullable()->after('type');
            $table->string('paraf')->nullable()->after('description');
            $table->string('ket')->nullable()->after('paraf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['voucher_number', 'paraf', 'ket']);
        });
    }
};
