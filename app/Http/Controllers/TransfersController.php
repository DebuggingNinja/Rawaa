<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Ledger;
use App\Models\Transfer;
use App\Services\Financials\BalanceCalculator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransfersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('can:view transfers')->only('index');
    $this->middleware('can:add transfers')->only('create', 'store');
    $this->middleware('can:update transfers')->only('update', 'edit');
    $this->middleware('can:delete transfers')->only('destroy');
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
    $transfers = Transfer::where([
      ['id', '!=', Null],
      [function ($query) use ($request) {
        if (($term = $request->search)) {
          $query->orWhere('from', 'LIKE', '%' . $term . '%')
            ->orWhere('date', 'LIKE', '%' . $term . '%')
            ->get();
        }
      }]
    ])->paginate($per_page);
    $title = "Transfers";
    $description = "Show Transfers";
    // remember to remove clients
    return view('transfers.index', compact('title', 'description', 'transfers'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $title = "Create new Transfer";
    $description = "Create New Transfer Page";
    $clients = Client::all();
    return view('transfers.create', compact('title', 'description', 'clients'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $this->validate($request, [
      'from' => 'required',
      'date' => 'date',
      'exchange_rate' => 'required|numeric',
      'transfer_usd' => 'required|numeric',
      'transfer_rmb' => 'required|numeric',
      'transfers' => 'required|array',
      'transfers.*.client_id' => 'required|exists:clients,id',
      'transfers.*.description' => 'required',
      'transfers.*.amount_rmb' => 'required|numeric',
      'transfers.*.amount_usd' => 'required|numeric',
    ]);
    try {
      DB::beginTransaction();
      $transfer = Transfer::create([
        'from' => $request->from,
        'date' => $request->date,
        'rate' => $request->exchange_rate,
        'amount_rmb' => $request->transfer_rmb,
        'amount_usd' => $request->transfer_usd
      ]);
      foreach ($request->transfers as $transferData) {
        $client = Client::findOrFail($transferData['client_id']);
        $ledger = Ledger::create([
          'date' => now(),
          'description' => $transferData['description'],
          'credit' => $transferData['amount_rmb'],
          'balance' => 0,
          'action' => 'transfer',
          'client_id' => $client->id,
          'transfer_id' => $transfer->id
        ]);
        BalanceCalculator::Client($client->id);
      }

      DB::commit();
      if (app()->getLocale() == 'en') {
        //toastr()->success('Transfer Created Successfully');
      } else {
        //toastr()->success('تم إنشاء الحوالة بنجاح');
      }
      return redirect()->route('transfers.index')->with('create', 'Transfer Created Successfully');
    } catch (Exception $e) {
      DB::rollBack();
      if (app()->getLocale() == 'en') {
        //toastr()->error('Error occured Try Again!');
      } else {
        //toastr()->error('عذرا هناك خطأ حاول مرة أخرى');
      }
      return back();
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $transfer = Transfer::with('ledgers.client')->findOrFail($id);
    $title = "Transfer";
    $description = "Transfer Page";
    return view('transfers.show', compact('title', 'description', 'transfer'));
  }
}
