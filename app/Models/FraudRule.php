<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraudRule extends Model
{
    protected $fillable = [
        'name',
        'rule_type',
        'value',
        'risk_score',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'risk_score' => 'integer',
        'is_active' => 'boolean',
    ];
}
