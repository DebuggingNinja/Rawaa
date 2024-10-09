<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPaymentReminder extends Model
{
  use HasFactory;

  protected $fillable = ['supplier_id', 'description', 'payment_date', 'status'];

  public function supplier()
  {
    return $this->belongsTo(Supplier::class);
  }
  protected static function booted()
  {
    static::retrieved(function ($model) {
      // Check if the payment date has passed and status is pending
      if ($model->status === 'pending' && Carbon::now()->gt(Carbon::parse($model->payment_date)->addDay(1))) {
        // Update the status to "overdue"
        $model->update(['status' => 'overdue']);
      }
      if ($model->status === 'overdue' && Carbon::now()->lte(Carbon::parse($model->payment_date)->addDay(1))) {
        // Update the status to "pending"
        $model->update(['status' => 'pending']);
      }
      if ($model->supplier->balance == 0) {
        // Update the status to "pending"
        $model->update(['status' => 'paid']);
      }
    });
  }
}