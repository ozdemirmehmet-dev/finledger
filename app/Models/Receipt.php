<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'company_id',
        'file_path',
        'status',
        'extracted_amount',
        'extracted_date',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'extracted_amount' => 'decimal:2',
            'extracted_date'   => 'date',
            'processed_at'     => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
