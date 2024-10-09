<?php

namespace App\Http\Controllers;

use App\Enums\FinancialType;
use App\Models\ShippingCompany;
use App\Models\Supplier;
use App\Models\CompanyPaymentReminder;
use Illuminate\Http\Request;

class CompanyPaymentReminderController extends Controller
{

  public function index(Request $request)
  {
    $per_page = (!empty(session('pagination_per_page'))) ? session('pagination_per_page') : 20;
    $allPaymentReminders = CompanyPaymentReminder::with(['company'])->whereIn('status', ['pending', 'overdue']);
    $paymentReminders =  $allPaymentReminders->orderBy('payment_date')->whereHas('company', function ($query) use ($request) {
      if (($term = $request->search))
        return $query->orWhere('name', 'LIKE', '%' . $term . '%')
          ->orWhere('phone', 'LIKE', '%' . $term . '%')
          ->orWhere('code', 'LIKE', '%' . $term . '%');
    })->get();

    $companies = ShippingCompany::where([
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

    $scheduledSuppliers = $allPaymentReminders->get()->pluck('shipping_company_id')->toArray();

    if ($scheduledSuppliers != [])
      $companies = $companies->whereNotIn('id', $scheduledSuppliers);

    $companies = $companies->paginate($per_page)->onEachSide(1);
    $title = __('company.companies\' Payments Reminders');
    $description = __('company.companies\' Payments Reminders');
    return view('company_payment_reminder.index', compact('title', 'description', 'companies', 'paymentReminders'));
  }

  public function changeDate(Request $request)
  {

    $request->validate([
      'new_payment_date' => ['required', 'date']
    ]);
    CompanyPaymentReminder::findOrFail($request->id)->update(['payment_date' => $request->new_payment_date]);
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
      'shipping_company_id'      => ['required', 'exists:companies,id']
    ]);
    CompanyPaymentReminder::create([
      'payment_date'  => $request->new_payment_date,
      'shipping_company_id'   => $request->shipping_company_id,
    ]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Date Created Successfully');
    } else {
      //toastr()->success('تم انشاء الموعد بنجاح');
    }
    return redirect()->back();
  }
}
