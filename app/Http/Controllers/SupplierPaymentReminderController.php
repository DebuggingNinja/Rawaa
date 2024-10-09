<?php

namespace App\Http\Controllers;

use App\Enums\FinancialType;
use App\Models\Supplier;
use App\Models\SupplierPaymentReminder;
use Illuminate\Http\Request;

class SupplierPaymentReminderController extends Controller
{

  public function index(Request $request)
  {
    $per_page = (!empty(session('pagination_per_page'))) ? session('pagination_per_page') : 20;
    $allPaymentReminders = SupplierPaymentReminder::with(['supplier'])->whereIn('status', ['pending', 'overdue']);
    $paymentReminders =  $allPaymentReminders->orderBy('payment_date')->whereHas('supplier', function ($query) use ($request) {
      if (($term = $request->search))
        return $query->orWhere('name', 'LIKE', '%' . $term . '%')
          ->orWhere('phone', 'LIKE', '%' . $term . '%')
          ->orWhere('code', 'LIKE', '%' . $term . '%');
    })->get();

    $suppliers = Supplier::where([
      ['name', '!=', Null],
      ['balance', '>', '0'], [function ($query) use ($request) {
        if (($term = $request->search)) {
          $query->orWhere('name', 'LIKE', '%' . $term . '%')
            ->orWhere('phone', 'LIKE', '%' . $term . '%')
            ->orWhere('code', 'LIKE', '%' . $term . '%')
            ->get();
        }
      }]
    ]);

    $scheduledSuppliers = $allPaymentReminders->get()->pluck('supplier_id')->toArray();

    if ($scheduledSuppliers != [])
      $suppliers = $suppliers->whereNotIn('id', $scheduledSuppliers);

    $suppliers = $suppliers->paginate($per_page)->onEachSide(1);
    $title = __('supplier.Suppliers\' Payments Reminders');
    $description = __('supplier.Suppliers\' Payments Reminders');
    return view('supplier_payment_reminder.index', compact('title', 'description', 'suppliers', 'paymentReminders'));
  }

  public function changeDate(Request $request)
  {

    $request->validate([
      'new_payment_date' => ['required', 'date']
    ]);
    SupplierPaymentReminder::findOrFail($request->id)->update(['payment_date' => $request->new_payment_date]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Date Changed Successfully');
    } else {
      //toastr()->success('تم تغيير الموعد بنجاح');
    }
    return redirect()->back();
  }

  public function scheduleDate(Request $request)
  {
    $request->validate([
      'new_payment_date' => ['required', 'date'],
      'supplier_id'      => ['required', 'exists:suppliers,id']
    ]);
    SupplierPaymentReminder::create([
      'payment_date'  => $request->new_payment_date,
      'supplier_id'   => $request->supplier_id,
    ]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Date Created Successfully');
    } else {
      //toastr()->success('تم انشاء الموعد بنجاح');
    }
    return redirect()->back();
  }
}
