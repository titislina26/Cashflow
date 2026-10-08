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
}
