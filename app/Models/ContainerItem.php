<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ContainerItem extends Model
{
  use HasFactory;

  protected $fillable = [
    'meter_price',
    'total',
    'notes',
    'item_id',
    'container_id'
  ];

  public function container(): BelongsTo
  {
    return $this->belongsTo(Container::class, 'container_id');
  }
  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class, 'item_id');
  }
  protected static function boot()
  {
    parent::boot();
    static::creating(function ($containerItem) {
      DB::transaction(function () use ($containerItem) {
        // Increase the client's balance when a new ContainerItem is created
        $client = $containerItem->item->order->client;
        $previousBalance = $client->ledgers()->latest('date')->first()->balance;
        Ledger::create([
          'date' => now(),
          'action' => 'shipping',
          'description' => "Shipping for Item #" . $containerItem->item?->item ?? "--",
          'debit' => $containerItem->total,
          'balance' => $previousBalance - $containerItem->total,
          'client_id' => $client->id
        ]);
      });
    });
    static::deleting(function ($containerItem) {
      DB::transaction(function () use ($containerItem) {
        $client = $containerItem->item->order->client;
        $previousBalance = $client->ledgers()->latest('date')->first()->balance;
        Ledger::create([
          'date' => now(),
          'action' => 'delete',
          'description' => "Deleting for Shipping Item #" . $containerItem->item->item,
          'credit' => $containerItem->total,
          'balance' => $previousBalance + $containerItem->total,
          'client_id' => $client->id
        ]);
      });
    });
  }
}
