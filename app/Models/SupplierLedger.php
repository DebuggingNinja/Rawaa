<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierLedger extends Model
{
  use HasFactory;
  protected $fillable = ['date', 'description', 'debit', 'credit', 'balance', 'supplier_id', 'action'];

  public function supplier(): BelongsTo
  {
    return $this->belongsTo(Supplier::class);
  }
}
