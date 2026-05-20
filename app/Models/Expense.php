<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_number', 'category_id', 'account_id', 'amount', 'tax_amount', 'total',
        'date', 'description', 'payment_method', 'payment_account_id', 'reference',
        'attachment', 'is_recurring', 'recurring_interval', 'next_due_date', 'journal_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'date' => 'date',
        'next_due_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(!empty($filters['category_id']), fn (Builder $q) => $q->where('category_id', $filters['category_id']))
            ->when(!empty($filters['payment_method']), fn (Builder $q) => $q->where('payment_method', $filters['payment_method']))
            ->when(!empty($filters['date_from']), fn (Builder $q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn (Builder $q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->when(!empty($filters['search']), function (Builder $q) use ($filters): void {
                $s = '%'.$filters['search'].'%';
                $q->where(function (Builder $q2) use ($s): void {
                    $q2->where('expense_number', 'like', $s)
                        ->orWhere('description', 'like', $s)
                        ->orWhere('reference', 'like', $s);
                });
            });
    }
}

