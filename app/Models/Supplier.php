<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
  use HasFactory;
  protected $fillable = [
    'name',
    'code',
    'phone',
    'store_number',
    'balance'
  ];
  public function items(): HasMany
  {
    return $this->hasMany(Item::class);
  }
  public function ledgers()
  {
    return $this->hasMany(SupplierLedger::class);
  }

  protected static function boot()
  {
    parent::boot();
    static::created(function ($supplier) {
      DB::transaction(function () use ($supplier) {
        SupplierLedger::create([
          'date' => now(),
          'description' => "Openning Account",
          'credit' => 0,
          'debit' => 0,
          'action' => 'opining',
          'balance' => 0,
          'supplier_id' => $supplier->id
        ]);
      });
    });
  }
}
