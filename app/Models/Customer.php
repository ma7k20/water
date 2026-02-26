<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'meter_number',
        'phone',
        'service_type',
        'unit_price',
        'previous_reading',
        'previous_reading_date',
        'previous_balance',
        'status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'previous_reading' => 'decimal:2',
        'previous_reading_date' => 'date',
        'previous_balance' => 'decimal:2',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(BalanceTransaction::class);
    }
}
