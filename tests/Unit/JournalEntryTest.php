<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JournalEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_journal_entry_creates_balanced_transactions_and_cascades_delete()
    {
        $catExpense = Category::create([
            'code' => '5-4120',
            'name' => 'Beban Penyusutan Gedung',
            'type' => 'expense',
            'color' => '#ef4444',
            'icon' => '🏢',
        ]);

        $catContra = Category::create([
            'code' => '1-2311',
            'name' => 'Akumulasi Penyusutan Gedung',
            'type' => 'income',
            'color' => '#22c55e',
            'icon' => '📉',
        ]);

        $journalNumber = 'TEST-JU-' . time();
        $amount = 2500000;

        $entry = JournalEntry::create([
            'journal_number' => $journalNumber,
            'date' => now()->toDateString(),
            'memo' => 'Test Penyusutan Gedung Kampus',
            'total_amount' => $amount,
        ]);

        $txDebit = Transaction::create([
            'account' => 'general_journal',
            'type' => 'expense',
            'amount' => $amount,
            'category_id' => $catExpense->id,
            'date' => now()->toDateString(),
            'description' => 'Test Penyusutan Gedung (Debet)',
            'voucher_number' => $journalNumber,
            'journal_entry_id' => $entry->id,
        ]);

        $txCredit = Transaction::create([
            'account' => 'general_journal',
            'type' => 'income',
            'amount' => $amount,
            'category_id' => $catContra->id,
            'date' => now()->toDateString(),
            'description' => 'Test Akumulasi Penyusutan (Kredit)',
            'voucher_number' => $journalNumber,
            'journal_entry_id' => $entry->id,
        ]);

        $this->assertEquals(2, $entry->transactions()->count());
        $this->assertEquals($amount, $txDebit->amount);
        $this->assertEquals($amount, $txCredit->amount);
        $this->assertEquals($entry->id, $txDebit->journal_entry_id);

        // Test cascade deletion
        $entryId = $entry->id;
        $entry->delete();

        $this->assertDatabaseMissing('journal_entries', ['id' => $entryId]);
        $this->assertDatabaseMissing('transactions', ['id' => $txDebit->id]);
        $this->assertDatabaseMissing('transactions', ['id' => $txCredit->id]);
    }
}
