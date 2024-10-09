<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientClaimReminder;
use Illuminate\Http\Request;

class ClientClaimReminderController extends Controller
{
  public function index(Request $request)
  {
    $per_page = (!empty(session('pagination_per_page'))) ? session('pagination_per_page') : 20;
    $allPaymentReminders = ClientClaimReminder::with(['client'])->whereIn('status', ['pending', 'overdue']);
    $paymentReminders =  $allPaymentReminders->orderBy('due_date')->whereHas('client', function ($query) use ($request) {
      if (($term = $request->search))
        return $query->orWhere('name', 'LIKE', '%' . $term . '%')
          ->orWhere('phone', 'LIKE', '%' . $term . '%')
          ->orWhere('code', 'LIKE', '%' . $term . '%');
    })->get();

    $clients = Client::where([
      ['name', '!=', Null],
      ['balance', '<', '0'],
      [function ($query) use ($request) {
        if (($term = $request->search)) {
          $query->orWhere('name', 'LIKE', '%' . $term . '%')
            ->orWhere('phone', 'LIKE', '%' . $term . '%')
            ->orWhere('code', 'LIKE', '%' . $term . '%')
            ->get();
        }
      }]
    ]);

    $scheduledClients = $allPaymentReminders->get()->pluck('client_id')->toArray();

    if ($scheduledClients != [])
      $clients = $clients->whereNotIn('id', $scheduledClients);

    $clients = $clients->paginate($per_page)->onEachSide(1);
    $title = __('client.Clients\' Claim Reminders');
    $description = __('client.Clients\' Claim Reminders');
    return view('client_claim_reminder.index', compact('title', 'description', 'clients', 'paymentReminders'));
  }

  public function changeDate(Request $request)
  {

    $request->validate([
      'new_payment_date' => ['required', 'date']
    ]);
    ClientClaimReminder::findOrFail($request->id)->update(['due_date' => $request->new_payment_date]);
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
      'client_id'      => ['required', 'exists:clients,id']
    ]);
    ClientClaimReminder::create([
      'due_date'  => $request->new_payment_date,
      'client_id'   => $request->client_id,
    ]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Date Created Successfully');
    } else {
      //toastr()->success('تم انشاء الموعد بنجاح');
    }
    return redirect()->back();
  }
}
