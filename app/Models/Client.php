<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'mark',
        'phone',
        'phone2',
        'email',
        'address',
        'balance'
    ];

  public function ledgers()
  {
    return $this->hasMany(Ledger::class);
  }
  public function orders()
  {
    return $this->hasMany(Order::class);
  }
  public function containers()
  {
    return $this->hasMany(ContainerClient::class);
  }
  protected static function boot()
  {
    parent::boot();
    static::created(function ($client) {
      DB::transaction(function () use ($client) {
        Ledger::create([
          'date' => now(),
          'description' => "Openning Account",
          'credit' => 0,
          'debit' => 0,
          'action' => 'opining',
          'balance' => 0,
          'client_id' => $client->id
        ]);
      });
    });
  }
}
