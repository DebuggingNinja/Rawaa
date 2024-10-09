<?php

namespace App\Services\Financials;

use App\Models\Client;
use App\Models\Container;
use App\Models\ContainerItem;
use App\Models\Item;
use App\Models\Ledger;
use App\Models\ShipperLedger;
use App\Models\ShippingCompany;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use Carbon\Carbon;

class BalanceCalculator
{

  public static function Client($id)
  {
    try {
      $client = Client::with(['ledgers' => function ($query)  {
        $query->whereIn('action', ['credit', 'debit', 'transfer']);
        $query->orderBy('date');
      }, 'containers' => function ($query){
      }, 'orders' => function ($query){
        $query->where('commission', '>', 0);
      }])->find($id);

      $balance = 0;
      $client->ledgers->map(function ($item) use(&$balance){
        $credit = $item['credit'];
        $debit = $item['debit'];

        if ($item['currency'] == 'usd'){
          $credit = $credit * $item['currency_rate'];
          $debit = $debit * $item['currency_rate'];
        }

        if($credit){
          $balance += $credit;
        }
        if($debit){
          $balance -= $debit;
        }

        return $item;

      });

      Item::where('status', 'shipped')->whereHas('order', fn($q) => $q->where('client_id', $client->id))
        ->get()->map(function ($item) use(&$balance){
          $balance -= $item->total;
        });

      $client->orders->map(function ($item) use(&$balance){
          if($item->items->count() == $item->items->whereIn('status', ['shipped', 'cancelled'])->count()){
            $total = $item->items->whereIn('status', ['shipped', 'cancelled'])->sum('total');
            $total = ($total / 100) * $item->commission;
            $balance -= $total;
          }
        });

      $client->containers->map(function ($item) use(&$balance) {
        $balance -= $item->total;
      });

      $client->update(['balance' => $balance]);
    } catch (\Throwable $th) {
      dd($th->getMessage());
    }
  }

  public static function Supplier($id)
  {
    try {

      $supplier = Supplier::with(['ledgers' => function ($query) {
        $query->whereIn('action', ['credit', 'debit']);
      }])->find($id);

      $balance = 0;
      $supplier->ledgers->map(function ($item) use(&$balance){
        $credit = $item['credit'];
        $debit = $item['debit'];

        if ($item['currency'] == 'usd'){
          $credit = $credit * $item['currency_rate'];
          $debit = $debit * $item['currency_rate'];
        }

        if($credit){
          $balance += $credit;
        }
        if($debit){
          $balance -= $debit;
        }

        return $item;

      });

      Item::whereIn('status', ['shipped', 'received'])->where('supplier_id', $supplier->id)
        ->get()->map(function ($item) use(&$balance){
          $balance += $item->total;
        });

      $supplier->update(['balance' => $balance]);
    } catch (\Throwable $th) {
    }
  }

  public static function company($id)
  {
    try {

      $company = ShippingCompany::with(['ledgers' => function ($query) {
        $query->whereIn('action', ['credit', 'debit']);
      }])->find($id);

      $balance = 0;
      $company->ledgers->map(function ($item) use(&$balance){
        $credit = $item['credit'];
        $debit = $item['debit'];

        if ($item['currency'] == 'usd'){
          $credit = $credit * $item['currency_rate'];
          $debit = $debit * $item['currency_rate'];
        }

        if($credit){
          $balance += $credit;
        }
        if($debit){
          $balance -= $debit;
        }

        return $item;

      });

      Container::where('shipping_company_id', $company->id)
        ->get()->map(function ($item) use(&$balance){
          $balance += $item->cost_rmb;
        });

      $company->update(['balance' => $balance]);
    } catch (\Throwable $th) {
    }
  }
}
