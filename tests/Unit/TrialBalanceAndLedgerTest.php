<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ReportController;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrialBalanceAndLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_trial_balance_and_general_ledger_calculations()
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

        $journalNumber = 'JU-TEST-' . time();
        $amount = 5000000;

        $entry = JournalEntry::create([
            'journal_number' => $journalNumber,
            'date' => '2026-09-15',
            'memo' => 'Test Penyusutan',
            'total_amount' => $amount,
        ]);

        Transaction::create([
            'account' => 'general_journal',
            'type' => 'expense',
            'amount' => $amount,
            'category_id' => $catExpense->id,
            'date' => '2026-09-15',
            'description' => 'Beban Penyusutan (Debet)',
            'voucher_number' => $journalNumber,
            'journal_entry_id' => $entry->id,
        ]);

        Transaction::create([
            'account' => 'general_journal',
            'type' => 'income',
            'amount' => $amount,
            'category_id' => $catContra->id,
            'date' => '2026-09-15',
            'description' => 'Akumulasi Penyusutan (Kredit)',
            'voucher_number' => $journalNumber,
            'journal_entry_id' => $entry->id,
        ]);

        $controller = new ReportController();

        // 1. Trial Balance Test
        $tb = $controller->getTrialBalanceData('2026-01-01', '2026-12-31');
        $this->assertEquals(0, $tb['outOfBalance']);
        $this->assertEquals($amount, $tb['grandTotalDebit']);
        $this->assertEquals($amount, $tb['grandTotalCredit']);

        // 2. General Ledger Test
        $gl = $controller->getGeneralLedgerData($catExpense->id, '2026-01-01', '2026-12-31');
        $this->assertCount(1, $gl['ledgerData']);
        $acc = $gl['ledgerData'][0];
        $this->assertEquals($amount, $acc['total_debit']);
        $this->assertEquals(0, $acc['total_credit']);
        $this->assertEquals($amount, $acc['ending_balance']);
    }

    public function test_journal_entry_with_cash_account_balances_correctly()
    {
        $catKas = Category::create([
            'code' => '1-1110',
            'name' => 'Kas',
            'type' => 'expense',
            'color' => '#f59e0b',
            'icon' => '💵',
        ]);

        $catBantuan = Category::create([
            'code' => '4-2030',
            'name' => 'Bantuan Tidak Terikat',
            'type' => 'income',
            'color' => '#84cc16',
            'icon' => '🎁',
        ]);

        $response = $this->post(route('journal-entries.store'), [
            'journal_number' => 'JU-CASH-001',
            'date' => '2026-09-29',
            'memo' => 'Bantuan Masuk Kas',
            'lines' => [
                [
                    'category_id' => $catKas->id,
                    'debit' => 500000,
                    'credit' => 0,
                    'description' => 'Kas bertambah (Debet)'
                ],
                [
                    'category_id' => $catBantuan->id,
                    'debit' => 0,
                    'credit' => 500000,
                    'description' => 'Bantuan (Kredit)'
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();

        $controller = new ReportController();
        $tb = $controller->getTrialBalanceData('2026-01-01', '2026-12-31');
        $this->assertEquals(0, $tb['outOfBalance']);
        $this->assertEquals(500000, $tb['grandTotalDebit']);
        $this->assertEquals(500000, $tb['grandTotalCredit']);

        $gl = $controller->getGeneralLedgerData('all', '2026-01-01', '2026-12-31');
        $this->assertEquals(500000, $gl['grandTotalDebit']);
        $this->assertEquals(500000, $gl['grandTotalCredit']);

        $kasLedger = collect($gl['ledgerData'])->firstWhere('category.code', '1-1110');
        $this->assertNotNull($kasLedger);
        $this->assertEquals(500000, $kasLedger['total_debit']);
        $this->assertEquals(0, $kasLedger['total_credit']);
        $this->assertEquals(500000, $kasLedger['ending_balance']);
    }
}
