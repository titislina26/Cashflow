<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['code', 'name', 'type', 'icon', 'color'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getFullLabelAttribute(): string
    {
        return $this->code ? "[{$this->code}] {$this->name}" : $this->name;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
              ->orWhere('name', 'like', "%{$term}%");
        });
    }
}
