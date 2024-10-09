<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
  use HasFactory;
  protected $fillable = [
    'code',
    'file',
    'registery',
    'paper',
    'type',
    'repository_id',
    'client_id',
    'commission',

  ];

  public function items(): HasMany
  {
    return $this->hasMany(Item::class, 'order_id');
  }
  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function repo(): BelongsTo
  {
    return $this->belongsTo(Repository::class, 'repository_id');
  }
  public function containerItems()
  {
    return $this->hasManyThrough(ContainerItem::class, Item::class, '');
  }
  protected static function booted()
  {
    parent::boot();
    static::deleting(function ($order) {
      DB::transaction(function () use ($order) {
        // Check the order type
        $total = ($order->type == 'buy_ship')
          ? $order->calculateTotalFromItems()
          : $order->calculateTotalFromContainerItems();

        $client = $order->client;
        $previousBalance = $client->ledgers()->latest('date')->first()->balance;
        Ledger::create([
          'date' => now(),
          'description' => "Deleting Order #$order->id",
          'credit' => $total,
          'action' => 'delete',
          'balance' => $previousBalance + $total,
          'client_id' => $client->id
        ]);
      });
    });
  }

  private function calculateTotalFromItems()
  {
    return $this->items()->sum('total');
  }
  private function calculateTotalFromContainerItems()
  {
    return $this->items->sum(function ($item) {
      return optional($item->containerItem)->total ?? 0;
    });
  }

  public function generateBuyShipSerial(){
    $year = Setting::where('key', 'year')->value('value');
    $lastRegistry = self::where('type', 'buy_ship')->where('code', 'LIKE', "%{$year}-%")
      ->orderBy('registery', 'desc')->first()?->registery ?? 1;
    $lastPaper = self::where('type', 'buy_ship')->where('code', 'LIKE', "%{$year}-%")
      ->where('registery', $lastRegistry)->orderBy('paper', 'desc')->first()?->paper ?? 0;
    $lastPaper++;
    if($lastPaper > 30){
      $lastRegistry++;
      $lastPaper = 1;
    }
    $serial = $year . "-" . $lastRegistry . "-" . $lastPaper;
    while(self::where('type', 'buy_ship')->where('code', $serial)->first()){
      if($lastPaper > 30){
        $lastRegistry++;
        $lastPaper = 0;
      }
      $serial = $year . "-" . $lastRegistry . "-" . ++$lastPaper;
    }
    $this->code = $serial;
    $this->registery = $lastRegistry;
    $this->paper = $lastPaper;
    $this->save();
    $this->refresh();
  }

  public function generateShipSerial(){
    $year = Setting::where('key', 'year')->value('value');
    $start = self::where('type', 'ship')->where('code', 'LIKE', "%S-$year%")->count() + 1;
    $serial = "S-" . $year . "-" . $start;
    while(self::where('type', 'ship')->where('code', $serial)->first()) $serial = "S-" . $year . "-" . ++$start;
    $this->code = $serial;
    $this->save();
    $this->refresh();
  }

}
