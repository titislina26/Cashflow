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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number')->unique();
            $table->date('date');
            $table->text('memo')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('related_transaction_id')->constrained('journal_entries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn('journal_entry_id');
        });

        Schema::dropIfExists('journal_entries');
    }
};
