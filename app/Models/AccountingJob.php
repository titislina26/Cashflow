<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingJob extends Model
{
    protected $table = 'accounting_jobs';

    protected static function booted()
    {
        static::addGlobalScope('academicOrder', function ($builder) {
            $builder->orderByRaw("
                CASE 
                    WHEN code = '002' OR name LIKE '%TS%' OR name LIKE '%Sipil%' THEN 1
                    WHEN code = '004' OR name LIKE '%TL%' OR name LIKE '%Lingkungan%' THEN 2
                    WHEN code = '010' OR name LIKE '%TI%' OR name LIKE '%Informatika%' THEN 3
                    WHEN code = '009' OR name LIKE '%Pusat%' OR description LIKE '%Pusat%' THEN 99
                    ELSE 50
                END ASC
            ");
        });
    }

    public function scopeAcademicOrder($query)
    {
        return $query->orderByRaw("
            CASE 
                WHEN code = '002' OR name LIKE '%TS%' OR name LIKE '%Sipil%' THEN 1
                WHEN code = '004' OR name LIKE '%TL%' OR name LIKE '%Lingkungan%' THEN 2
                WHEN code = '010' OR name LIKE '%TI%' OR name LIKE '%Informatika%' THEN 3
                WHEN code = '009' OR name LIKE '%Pusat%' OR description LIKE '%Pusat%' THEN 99
                ELSE 50
            END ASC
        ");
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'job_id');
    }
}
