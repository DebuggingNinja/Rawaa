<?php

namespace App\Models;

use App\Services\Financials\BalanceCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
  use HasFactory;
  protected $fillable = [
    'carton_code',
    'item',
    'carton_quantity',
    'pieces_number',
    'single_price',
    'total',
    'check_date',
    'cbm',
    'weight',
    'status',
    'check_image',
    'container_number',
    'check_notes',
    'receive_notes',
    'cancelled_notes',
    'notes',
    'receive_date',
    'shipping_date',
    'order_id',
    'mark',
    'supplier_id',
    'collect_by',
  ];

  protected $casts = [
    'shipping_date' => 'datetime',
  ];

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class, 'order_id');
  }
  public function containerItem(): HasOne
  {
    return $this->hasOne(ContainerItem::class);
  }
  public function supplier(): BelongsTo
  {
    return $this->belongsTo(Supplier::class, 'supplier_id');
  }

  protected static function boot()
  {
    parent::boot();
    static::updating(function ($item) {
      DB::transaction(function () use ($item) {
        if ($item->supplier_id != null) {
          $oldStatus = $item->getOriginal('status');
          $newStatus = $item->status;
          $client = $item->order->client;
          $supplier = Supplier::find($item->supplier_id);

          if ($oldStatus == 'received' && $newStatus != 'shipped') {
            SupplierLedger::create([
              'date' => now(),
              'description' => "Deleting Item #$item->item",
              'debit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'delete',
              'supplier_id' => $supplier->id
            ]);
            BalanceCalculator::Supplier($supplier->id);
          }
          if ($newStatus == 'received') {
            SupplierLedger::create([
              'date' => now(),
              'description' => "Price for Item #$item->item",
              'credit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'item_calculate',
              'supplier_id' => $supplier->id
            ]);
            BalanceCalculator::Supplier($supplier->item);
          }
          if ($oldStatus == 'shipped') {
            $item->containerItem?->delete();
            Ledger::create([
              'date' => now(),
              'description' => "Removing Item #$item->item",
              'credit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'delete',
              'client_id' => $client->id
            ]);
            SupplierLedger::create([
              'date' => now(),
              'description' => "Removing Item #$item->item",
              'debit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'delete',
              'supplier_id' => $supplier->id
            ]);
            BalanceCalculator::Supplier($supplier->id);
            BalanceCalculator::Client($client->id);
          }
          if ($newStatus == 'shipped') {
            Ledger::create([
              'date' => now(),
              'description' => "Price for Item #$item->item",
              'debit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'shipping',
              'client_id' => $client->id
            ]);
            BalanceCalculator::Client($client->id);
          }
          if ($newStatus == 'cancelled') {
            Ledger::create([
              'date' => now(),
              'description' => "Cancel Item #$item->item",
              'credit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'cancel',
              'client_id' => $client->id
            ]);
            SupplierLedger::create([
              'date' => now(),
              'description' => "Cancel Item #$item->item",
              'debit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'cancel',
              'supplier_id' => $supplier->id
            ]);
            BalanceCalculator::Supplier($supplier->id);
            BalanceCalculator::Client($client->id);
          }
        }
      });
    });
    static::deleting(function ($item) {
      DB::transaction(function () use ($item) {
        if ($item->supplier_id != null) {
          $client = $item->order->client;
          $supplier = $item->supplier;
          $oldStatus = $item->getOriginal('status');

          if ($oldStatus ==  'shipped') {
            Ledger::create([
              'date' => now(),
              'description' => "Deleting Item #$item->item",
              'credit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'delete',
              'client_id' => $client->id
            ]);
            SupplierLedger::create([
              'date' => now(),
              'description' => "Deleting Item #$item->item",
              'debit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'delete',
              'supplier_id' => $supplier->id
            ]);
            BalanceCalculator::Supplier($supplier->id);
            BalanceCalculator::Client($client->id);
          }
          if ($oldStatus ==  'received') {
            SupplierLedger::create([
              'date' => now(),
              'description' => "Deleting Item #$item->item",
              'debit' => $item->total ?? 0,
              'balance' => 0,
              'action' => 'delete',
              'supplier_id' => $supplier->id
            ]);
            BalanceCalculator::Supplier($supplier->id);
          }

          if ($item->containerItem != null) {
            $item->containerItem->delete();
          }
        }
      });
    });
  }
}
