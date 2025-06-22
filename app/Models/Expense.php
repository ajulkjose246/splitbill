<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'title',
        'amount',
        'paid_by',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function shares()
    {
        return $this->hasMany(ExpenseShare::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'expense_shares')
                    ->withTimestamps();
    }
}
