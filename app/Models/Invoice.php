<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_type',
        'month',
        'year',
        'period_key',
        'billing_date',
        'previous_reading_date',
        'previous_reading',
        'current_reading',
        'consumption',
        'unit_price',
        'amount',
        'tax',
        'previous_balance',
        'new_balance',
        'whatsapp_status',
        'whatsapp_sent_at',
        'whatsapp_error',
        'is_locked',
    ];

    protected $casts = [
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'billing_date' => 'date',
        'previous_reading_date' => 'date',
        'whatsapp_sent_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
