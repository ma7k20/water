<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'status',
        'issued_at',
        'closed_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'closed_at' => 'datetime',
    ];
}
