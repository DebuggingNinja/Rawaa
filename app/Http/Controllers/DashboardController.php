<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientClaimReminder;
use App\Models\CompanyPaymentReminder;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Order;
use App\Models\Reminder;
use App\Models\Repository;
use App\Models\ShippingCompany;
use App\Models\Supplier;
use App\Models\SupplierPaymentReminder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

  /**
   * Display dashbnoard demo one of the resource.
   *
   * @return \Illuminate\View\View
   */
  public function index()
  {
    $title = "Dashboard Home";
    $description = "Dashboard Home Page";
    $repos = Repository::with(['orders.items' => function ($query) {
      $query->where('status', '=', 'received');
    }])->get();
//    $requestedItems = Order::with(['client', 'items' => function ($q) {
//      $q->where('status', 'requested')->whereNotNull('check_date')->orderBy('check_date', 'asc');
//    }])->whereHas('items', fn($q) => $q->where('status', 'requested'))->get();
    $requestedItemsCollection = Item::with(['order.client', 'supplier'])
      ->where('check_date', '>=', now()->format('Y-m-d'))
      ->where('status', 'requested')
      ->limit(8)
      ->orderBy('check_date', 'asc')
      ->get();

    $requestedItems = [];

    foreach ($requestedItemsCollection as $requestedItem){
      if(isset($requestedItems[$requestedItem->order?->id . "-" . $requestedItem->supplier?->id])) continue;
      $requestedItems[$requestedItem->order?->id . " " . $requestedItem->supplier?->id] = $requestedItem;
    }

    $waitingItems = Item::with(['order.client'])
      ->where('receive_date', '!=', null)
      //->where('receive_date', '>=', today()->toDateString())
      ->where('status', 'waiting')
      ->orderBy('receive_date', 'asc')
      ->get();

    $SupplierPaymentReminders = $this->get10SupplerReminders();
    $CompanyPaymentReminders = $this->get10CompanyReminders();
    $ClientClaimReminders = $this->get10ClientReminders();

    $globalReminders = Reminder::orderBy('date')->get();
    return view('pages.dashboard.index', compact('title', 'description', 'repos',
      'requestedItems', 'waitingItems', 'globalReminders', 'ClientClaimReminders', 'SupplierPaymentReminders',
      'CompanyPaymentReminders'));
  }

  public function all_requested_items()
  {
    $title = "Requested items";
    $repos = Repository::with(['orders.items' => function ($query) {
      $query->where('status', '=', 'received');
    }])->get();
//    $requestedItems = Order::with(['client', 'items' => function ($q) {
//      $q->where('status', 'requested')->whereNotNull('check_date')->orderBy('check_date', 'asc');
//    }])->whereHas('items', fn($q) => $q->where('status', 'requested'))->get();
    $requestedItemsCollection = Item::with(['order.client', 'supplier'])
      ->where('check_date', '>=', now()->format('Y-m-d'))
      ->where('status', 'requested')
      ->orderBy('check_date', 'asc')
      ->get();

    $requestedItems = [];

    foreach ($requestedItemsCollection as $requestedItem){
      if(isset($requestedItems[$requestedItem->order?->id . "-" . $requestedItem->supplier?->id])) continue;
      $requestedItems[$requestedItem->order?->id . " " . $requestedItem->supplier?->id] = $requestedItem;
    }

    return view('pages.dashboard.all_requested_items', compact('title', 'repos',
      'requestedItems'));
  }

  public function deleteReminder($id)
  {
    $reminder = Reminder::findOrFail($id)->delete();
    return $reminder ? response()->json(['error' => false, 'message' => 'reminder deleted']) :
      response()->json(['error' => true, 'message' => 'failed to delete']);
  }
  public function addReminder(Request $request)
  {
    $request->validate([
      'description' => 'required',
      'date' => 'required|date|after:'.date("Y-m-d", strtotime("-1 day")),
    ]);
    Reminder::create([
      'description' => $request->description,
      'date' => $request->date,
    ]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Reminder Created Successfully');
    } else {
      //toastr()->success('تم إنشاء التذكير بنجاح');
    }
    return redirect()->route('dashboard.index')->with('create', 'Reminder Created Successfully');
  }


  public function get10SupplerReminders()
  {
    $allPaymentReminders = SupplierPaymentReminder::with(['supplier'])->whereIn('status', ['pending', 'overdue']);
    $paymentReminders = $allPaymentReminders->orderBy('payment_date')->get();
    // Get suppliers that are not already in payment reminders
    $scheduledSuppliers = $paymentReminders->pluck('supplier_id')->toArray();
    $suppliers = Supplier::where('balance', '<', '0')->whereNotIn('id', $scheduledSuppliers)->take(10 - count($paymentReminders))->get();

    // Merge payment reminders and suppliers into a single collection
    $mergedData = $paymentReminders->merge($suppliers)->map(
      function ($item) {
        return [
          'name'          => $item['name'] ?? $item['supplier']['name'] ?? '--',
          'code'          => $item['code'] ?? $item['supplier']['code'] ?? '--',
          'balance'       => $item['balance'] ?? $item['supplier']['balance'] ?? '--',
          'payment_date'  => $item['payment_date'] ?? '--',
          'status'        => $item['status'] ?? '--',
        ];
      }
    );

    return $mergedData;
  }

  public function get10CompanyReminders()
  {
    $allPaymentReminders = CompanyPaymentReminder::with(['company'])->whereIn('status', ['pending', 'overdue']);
    $paymentReminders = $allPaymentReminders->orderBy('payment_date')->get();
    // Get suppliers that are not already in payment reminders
    $scheduledSuppliers = $paymentReminders->pluck('shipping_company_id')->toArray();
    $suppliers = ShippingCompany::where('balance', '<', '0')->whereNotIn('id', $scheduledSuppliers)->take(10 - count($paymentReminders))->get();

    // Merge payment reminders and suppliers into a single collection
    $mergedData = $paymentReminders->merge($suppliers)->map(
      function ($item) {
        return [
          'name'          => $item['name'] ?? $item['company']['name'] ?? '--',
          'code'          => $item['code'] ?? $item['company']['code'] ?? '--',
          'balance'       => $item['balance'] ?? $item['supplier']['balance'] ?? '--',
          'payment_date'  => $item['payment_date'] ?? '--',
          'status'        => $item['status'] ?? '--',
        ];
      }
    );

    return $mergedData;
  }

  public function get10ClientReminders()
  {
    $allPaymentReminders = ClientClaimReminder::with(['client'])->whereIn('status', ['pending', 'overdue']);
    $paymentReminders = $allPaymentReminders->orderBy('due_date')->get();
    // Get suppliers that are not already in payment reminders
    $scheduledClients = $paymentReminders->pluck('client_id')->toArray();
    $clients = Client::where('balance', '>', '0')->whereNotIn('id', $scheduledClients)->take(10 - count($paymentReminders))->get();

    // Merge payment reminders and suppliers into a single collection
    $mergedData = $paymentReminders->merge($clients)->map(
      function ($item) {
        return [
          'name'          => $item['name'] ?? $item['client']['name'] ?? '--',
          'code'          => $item['code'] ?? $item['client']['code'] ?? '--',
          'balance'       => $item['balance'] ?? $item['client']['balance'] ?? '--',
          'payment_date'  => $item['due_date'] ?? '--',
          'status'        => $item['status'] ?? '--',
        ];
      }
    );

    return $mergedData;
  }
}
