<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingCompany extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'phone',
        'phone2',
        'email',
        'address',
        'balance',
    ];

  public function ledgers()
  {
    return $this->hasMany(ShipperLedger::class, 'shipping_company_id');
  }

}
