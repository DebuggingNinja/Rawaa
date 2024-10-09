<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ClientClaimReminder extends Model
{
  use HasFactory;

  protected $fillable = ['client_id', 'description', 'due_date', 'status'];

  public function client()
  {
    return $this->belongsTo(Client::class);
  }

  protected static function booted()
  {
    static::retrieved(function ($model) {
      // Check if the payment date has passed and status is pending
      if ($model->status === 'pending' && Carbon::now()->gt(Carbon::parse($model->due_date)->addDay(1))) {
        // Update the status to "overdue"
        $model->update(['status' => 'overdue']);
      }
      if ($model->status === 'overdue' && Carbon::now()->lte(Carbon::parse($model->due_date)->addDay(1))) {
        // Update the status to "pending"
        $model->update(['status' => 'pending']);
      }
      if ($model->client->balance == 0) {
        // Update the status to "pending"
        $model->update(['status' => 'paid']);
      }
    });
  }
}
