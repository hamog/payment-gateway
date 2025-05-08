<?php

namespace Hamog\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'type',
        'gateway',
        'amount',
        'transaction_id',
        'reference_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'float',
        'metadata' => 'array',
    ];

    /**
     * Scope a query to only include transactions of a specific gateway.
     */
    public function scopeGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope a query to only include transactions of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include successful transactions.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['COMPLETED', 'succeeded', 'captured', 'settled']);
    }

    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['FAILED', 'failed', 'declined', 'error']);
    }
}
