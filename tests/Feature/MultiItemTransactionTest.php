<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Transaction;

class MultiItemTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_store_multi_item_transaction_under_single_voucher()
    {
        $cat1 = Category::create([
            'code' => '6-1000',
            'name' => 'Biaya Kebersihan & Sampah',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#6366f1',
        ]);

        $cat2 = Category::create([
            'code' => '6-2000',
            'name' => 'Biaya Kirim JNE',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#6366f1',
        ]);

        $response = $this->post(route('transactions.store'), [
            'account' => 'petty_cash',
            'type' => 'expense',
            'date' => '2026-01-15',
            'voucher_number' => 'KT.01.26.001',
            'items' => [
                [
                    'category_id' => $cat1->id,
                    'amount' => 120000,
                    'description' => 'Pembayaran Sampah',
                ],
                [
                    'category_id' => $cat2->id,
                    'amount' => 100000,
                    'description' => 'Biaya Kirim JNE',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'account' => 'petty_cash',
            'voucher_number' => 'KT.01.26.001',
            'category_id' => $cat1->id,
            'amount' => 120000,
            'description' => 'Pembayaran Sampah',
        ]);

        $this->assertDatabaseHas('transactions', [
            'account' => 'petty_cash',
            'voucher_number' => 'KT.01.26.001',
            'category_id' => $cat2->id,
            'amount' => 100000,
            'description' => 'Biaya Kirim JNE',
        ]);

        $items = Transaction::where('voucher_number', 'KT.01.26.001')->get();
        $this->assertCount(2, $items);
        $this->assertEquals(220000, $items->sum('amount'));
    }

    public function test_single_item_transaction_still_works()
    {
        $cat = Category::create([
            'code' => '6-3000',
            'name' => 'Konsumsi Rapat',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#6366f1',
        ]);

        $response = $this->post(route('transactions.store'), [
            'account' => 'petty_cash',
            'type' => 'expense',
            'category_id' => $cat->id,
            'amount' => 75000,
            'date' => '2026-01-16',
            'voucher_number' => 'KT.01.26.002',
            'description' => 'Snack Rapat Koordinasi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'account' => 'petty_cash',
            'voucher_number' => 'KT.01.26.002',
            'amount' => 75000,
            'description' => 'Snack Rapat Koordinasi',
        ]);
    }

    public function test_can_soft_delete_and_restore_transaction()
    {
        $cat = Category::create([
            'code' => '6-4000',
            'name' => 'Biaya Sampah',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#6366f1',
        ]);

        $tx = Transaction::create([
            'account' => 'petty_cash',
            'type' => 'expense',
            'category_id' => $cat->id,
            'amount' => 120000,
            'date' => '2026-01-17',
            'voucher_number' => 'KT.01.26.003',
            'description' => 'Pembayaran Sampah',
        ]);

        // 1. Soft Delete
        $response = $this->delete(route('transactions.destroy', $tx->id));
        $response->assertRedirect();
        $response->assertSessionHas('success_deleted');

        // Harus hilang dari query reguler
        $this->assertNull(Transaction::find($tx->id));
        // Tapi tetap ada di database dengan deleted_at
        $this->assertNotNull(Transaction::withTrashed()->find($tx->id)->deleted_at);

        // 2. Restore
        $restoreResp = $this->post(route('transactions.restore', $tx->id));
        $restoreResp->assertRedirect();
        $restoreResp->assertSessionHas('success');

        // Kembali ada di query reguler
        $this->assertNotNull(Transaction::find($tx->id));
        $this->assertNull(Transaction::find($tx->id)->deleted_at);
    }

    public function test_can_force_delete_transaction()
    {
        $cat = Category::create([
            'code' => '6-5000',
            'name' => 'Biaya Lain',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#6366f1',
        ]);

        $tx = Transaction::create([
            'account' => 'petty_cash',
            'type' => 'expense',
            'category_id' => $cat->id,
            'amount' => 50000,
            'date' => '2026-01-18',
            'voucher_number' => 'KT.01.26.004',
            'description' => 'Biaya Lain-Lain',
        ]);

        $tx->delete();

        $forceResp = $this->delete(route('transactions.force-delete', $tx->id));
        $forceResp->assertRedirect();
        $forceResp->assertSessionHas('success');

        // Benar-benar hilang permanen dari database
        $this->assertNull(Transaction::withTrashed()->find($tx->id));
    }

    public function test_can_store_applicant_name_and_flow_to_voucher_recipient()
    {
        $cat = Category::create([
            'code' => '6-7000',
            'name' => 'Biaya Konsumsi Rapat',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#6366f1',
        ]);

        $response = $this->post(route('transactions.store'), [
            'account' => 'petty_cash',
            'type' => 'expense',
            'category_id' => $cat->id,
            'amount' => 75000,
            'date' => '2026-01-20',
            'voucher_number' => 'KT.01.26.010',
            'description' => 'Snack Rapat Koordinasi',
            'paraf' => 'Ahmad Fauzi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'voucher_number' => 'KT.01.26.010',
            'paraf' => 'Ahmad Fauzi',
        ]);

        $tx = Transaction::where('voucher_number', 'KT.01.26.010')->first();

        // Check voucher print preview inherits applicant name as recipient
        $voucherResp = $this->get(route('transactions.index', [
            'print_voucher' => 1,
            'transaction_id' => $tx->id,
        ]));

        $voucherResp->assertOk();
        $voucherResp->assertSee('Ahmad Fauzi');
    }

    public function test_can_store_multi_item_applicant_name()
    {
        $cat1 = Category::create([
            'code' => '6-8000',
            'name' => 'Biaya ATK',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#6366f1',
        ]);

        $cat2 = Category::create([
            'code' => '6-8001',
            'name' => 'Biaya Materai',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#6366f1',
        ]);

        $response = $this->post(route('transactions.store'), [
            'account' => 'petty_cash',
            'type' => 'expense',
            'date' => '2026-01-21',
            'voucher_number' => 'KT.01.26.011',
            'paraf' => 'Dewi Lestari',
            'items' => [
                [
                    'category_id' => $cat1->id,
                    'amount' => 50000,
                    'description' => 'Kertas A4',
                ],
                [
                    'category_id' => $cat2->id,
                    'amount' => 20000,
                    'description' => 'Materai 10000',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, Transaction::where('voucher_number', 'KT.01.26.011')->where('paraf', 'Dewi Lestari')->count());
    }
}

