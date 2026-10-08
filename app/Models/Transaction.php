<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'account', 'type', 'voucher_number', 'category_id', 'job_id',
        'amount', 'description', 'paraf', 'ket', 'date', 'attachment',
        'related_transaction_id', 'journal_entry_id'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function job()
    {
        return $this->belongsTo(AccountingJob::class, 'job_id');
    }

    public function relatedTransaction()
    {
        return $this->belongsTo(Transaction::class, 'related_transaction_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
