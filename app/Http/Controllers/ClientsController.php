<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ContainerClient;
use App\Models\ContainerItem;
use App\Models\Item;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;

class ClientsController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('can:view clients')->only('index');
    $this->middleware('can:add clients')->only('create', 'store');
    $this->middleware('can:update clients')->only('update', 'edit');
    $this->middleware('can:delete clients')->only('destroy');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $per_page = session('pagination_per_page');
    $per_page = (!empty($per_page)) ? $per_page : 20;
    $page     = (!empty($_GET['page'])) ? $_GET['page'] : 1;
    $offset   = ($page * $per_page) - $per_page;
    $clients = Client::where([
      ['name', '!=', Null],
      [function ($query) use ($request) {
        if (($term = $request->search)) {
          $query->orWhere('name', 'LIKE', '%' . $term . '%')
            ->orWhere('email', 'LIKE', '%' . $term . '%')
            ->orWhere('phone', 'LIKE', '%' . $term . '%')
            ->orWhere('phone2', 'LIKE', '%' . $term . '%')
            ->orWhere('mark', 'LIKE', '%' . $term . '%')
            ->orWhere('code', 'LIKE', '%' . $term . '%')
            ->get();
        }
      }]
    ])->paginate($per_page);
    $title = "Clients";
    $description = "Show Clients";
    return view('clients.index', compact('title', 'description', 'clients'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $title = "Create new Client";
    $description = "Create New Client Page";
    return view('clients.create', compact('title', 'description'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required',
      'code' => 'required|unique:clients,code',
      'email' => 'email|unique:clients|nullable',
      'phone' => 'nullable|unique:clients',
      'phone2' => 'nullable',
      'address' => 'nullable',
      'mark' => 'required',
    ]);
    Client::create([
      'name' => $request->name,
      'code' => $request->code,
      'email' => $request->email,
      'phone' => $request->phone,
      'phone2' => $request->phone2,
      'address' => $request->address,
      'mark' => $request->mark,
    ]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Client Created Successfully');
    } else {
      //toastr()->success('تم إنشاء العميل بنجاح');
    }
    return redirect()->route('clients.index',)->with('create', 'Client Created Successfully');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $title         = 'Show Client';
    $description   = 'Show Client Page';
    $client = Client::with('ledgers')->findOrFail($id);
    $ledgers = $client->ledgers->all();
    return view('clients.show', compact('title', 'description', 'client', 'ledgers'));
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $title         = 'Edit Client';
    $description   = 'Edit Client Page';
    $client = Client::findOrFail($id);
    return view('clients.edit', compact('title', 'description', 'client'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $request->validate([
      'name' => 'required',
      'code' => 'required|unique:clients,code,' . $id,
      'email' => 'nullable|email|unique:clients,email,' . $id,
      'phone' => 'nullable|unique:clients,phone,' . $id,
      'phone2' => 'nullable',
      'address' => 'nullable',
      'mark' => 'required',
    ]);
    $client = Client::findOrFail($id);
    $client->update([
      'name' => $request->name,
      'code' => $request->code,
      'email' => $request->email,
      'phone' => $request->phone,
      'phone2' => $request->phone2,
      'address' => $request->address,
      'mark' => $request->mark,
    ]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Client Updated Successfully');
    } else {
      //toastr()->success('تم تحديث العميل بنجاح');
    }
    return redirect()->route('clients.index')->with('update', 'Client Updated Successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $find_client = Client::findOrFail($id);
    $find_client->delete();
    if (app()->getLocale() == 'en') {
      //toastr()->success('Client Deleted Successfully');
    } else {
      //toastr()->success('تم حذف العميل بنجاح');
    }
    return redirect()->route('clients.index')->with('delete', 'Client Deleted Successfully');
  }

  public function download_ledger($id)
  {
    $client = Client::with('ledgers')->findOrFail($id);
    $ledgers = $client->ledgers->all();
    $fileBannerPath = Setting::where('key', 'file_banner')->value('value');
    if (!$fileBannerPath) {
      $fileBannerPath = 'ledger_file.jpg';
    }
    $date = now()->format('d-m-Y');
    $pdf = Pdf::loadView('clients.ledger-file', compact('client', 'ledgers', 'date', 'fileBannerPath'));
    return $pdf->download($client->name . '-' . $date . '.pdf');
  }

  public function statement(Request $request, $id)
  {

    $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::parse("- 1 month");
    $end = $request->end_time ? Carbon::parse($request->end_time) : now();

    $client = Client::with(['ledgers' => function ($query) use($start, $end) {
      $query->whereIn('action', ['credit', 'debit', 'transfer']);
      $query->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
      $query->orderBy('date');
    }, 'containers' => function ($query) use($start, $end) {
      $query->whereDate('created_at', '>=', $start)
        ->whereDate('created_at', '<=', $end);
    }, 'orders' => function ($query) use($start, $end) {
      $query->whereHas('items', fn($q) => $q->whereDate('created_at', '>=', $start)
        ->whereDate('created_at', '<=', $end))->where('commission', '>', 0);
    }])->findOrFail($id);

    $balance = 0;
    $ledgers = [];
    $transfer = trans('expense.transfer');
    $client->ledgers->map(function ($item) use(&$balance, &$ledgers, $transfer){
      $credit = $item['credit'];
      $debit = $item['debit'];

      if ($item['currency'] == 'usd'){
        $credit = $credit * $item['currency_rate'];
        $debit = $debit * $item['currency_rate'];
      }

      if($credit){
        $ledgers[Carbon::parse($item['date'])->format('Y-m-d H:i:s')][] = [
          "reason" => $item['description'] . ($item['action'] == "transfer" ? " - " . "(" . $transfer . ")" : ""),
          "credit" => $credit,
          "currency" => $item['currency']
        ];
        $balance += $credit;
      }
      if($debit){
        $ledgers[Carbon::parse($item['date'])->format('Y-m-d H:i:s')][] = [
          "reason" => $item['description'],
          "debit" => $debit,
          "currency" => $item['currency']
        ];
        $balance -= $debit;
      }

      return $item;

    });

    Item::where('status', 'shipped')
      ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
      ->whereHas('order', fn($q) => $q->where('client_id', $client->id))
      ->get()->map(function ($item) use(&$balance, &$ledgers){
        $ledgers[$item->created_at->format('Y-m-d H:i:s')][] = [
          "reason" => $item->item,
          "debit" => $item->total,
          "currency" => "rmb"
        ];
        $balance -= $item->total;
      });

    $client->containers->map(function ($item) use(&$balance, &$ledgers) {
      $ledgers[date("Y-m-d H:i:s", strtotime($item->container->shipping_date))][] = [
        "reason" => trans('company.shipping_for') . " " . $item->container->serial_number . " - " . $item->container->lock_number,
        "debit" => $item->total,
        "currency" => "rmb"
      ];
      $balance -= $item->total;
    });

    $client->orders->map(function ($item) use(&$balance, &$ledgers){
        if($item->items->count() == $item->items->whereIn('status', ['shipped', 'cancelled'])->count()){
          $total = $item->items->whereIn('status', ['shipped', 'cancelled'])->sum('total');
          $total = ($total / 100) * $item->commission;
          $ledgers[$item->created_at->format('Y-m-d H:i:s')][] = [
            "reason" => trans('order.company_commission'),
            "debit" => $total,
            "currency" => "rmb"
          ];
          $balance -= $total;
        }
      });

    $title = "Client Statement";
    $description = "Client Statement Page";
    ksort($ledgers);
    $total = 0;
    foreach ($ledgers as $key => &$ledger){
      foreach ($ledger as &$l){
        if(isset($l['debit'])) $total -= $l['debit'];
        if(isset($l['credit'])) $total += $l['credit'];
        $l["due"] = $total;
      }
    }
    return view('clients.statement', compact('client', 'title', 'description', 'ledgers', 'balance'));
  }
}
