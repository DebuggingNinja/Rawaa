<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Supplier;
use App\Services\Suppliers\SuppliersService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('can:view suppliers')->only('index');
    $this->middleware('can:add suppliers')->only('create', 'store');
    $this->middleware('can:update suppliers')->only('update', 'edit');
    $this->middleware('can:delete suppliers')->only('destroy');
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
    $suppliers = Supplier::where([
      ['name', '!=', Null],
      [function ($query) use ($request) {
        if (($term = $request->search)) {
          $query->orWhere('name', 'LIKE', '%' . $term . '%')
            ->orWhere('phone', 'LIKE', '%' . $term . '%')
            ->orWhere('code', 'LIKE', '%' . $term . '%')
            ->get();
        }
      }]
    ])->paginate($per_page);
    $title = "Suppliers";
    $description = "Show Suppliers";
    return view('suppliers.index', compact('title', 'description', 'suppliers'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $title = "Create new Supplier";
    $description = "Create New Supplier Page";
    return view('suppliers.create', compact('title', 'description'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function API_store(Request $request, SuppliersService $service)
  {
    $service->store($request);
    $suppliers = Supplier::all();
    if (app()->getLocale() == 'en')
      return response([
        'success'   => true,
        'message'   => 'Supplier Created Successfully',
        'suppliers' =>   $suppliers
      ]);
    return response([
      'success'   => true,
      'message'   => 'تم إنشاء المورد بنجاح',
      'suppliers' =>   $suppliers
    ]);
  }
  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request, SuppliersService $service)
  {
    $service->store($request);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Supplier Created Successfully');
    } else {
      //toastr()->success('تم إنشاء المورد بنجاح');
    }
    return redirect()->route('suppliers.index',)->with('create', 'Supplier Created Successfully');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $title         = 'Edit Supplier';
    $description   = 'Edit Supplier Page';
    $supplier = Supplier::findOrFail($id);
    return view('suppliers.edit', compact('title', 'description', 'supplier'));
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
      'code' => 'required|unique:suppliers,code,' . $id,
      'phone' => 'nullable|unique:suppliers,phone,' . $id,
      'store_number' => 'nullable'
    ]);
    $supplier = Supplier::findOrFail($id);
    $supplier->update([
      'name' => $request->name,
      'code' => $request->code,
      'phone' => $request->phone,
      'store_number' => $request->store_number
    ]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Supplier Updated Successfully');
    } else {
      //toastr()->success('تم تحديث المورد بنجاح');
    }
    return redirect()->route('suppliers.index')->with('update', 'Supplier Updated Successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $supplier = Supplier::findOrFail($id);
    $supplier->delete();
    if (app()->getLocale() == 'en') {
      //toastr()->success('Supplier Deleted Successfully');
    } else {
      //toastr()->success('تم حذف المورد بنجاح');
    }
    return redirect()->route('suppliers.index')->with('delete', 'Supplier Deleted Successfully');
  }


  public function statement(Request $request, $id)
  {

    $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::parse("- 1 month");
    $end = $request->end_time ? Carbon::parse($request->end_time) : now();

    $supplier = Supplier::with(['ledgers' => function ($query) use($start, $end) {
      $query->whereIn('action', ['credit', 'debit']);
      $query->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
      $query->orderBy('date');
    }])->findOrFail($id);

    $balance = 0;
    $ledgers = [];
    $supplier->ledgers->map(function ($item) use(&$balance, &$ledgers){
      $credit = $item['credit'];
      $debit = $item['debit'];

      if ($item['currency'] == 'usd'){
        $credit = $credit * $item['currency_rate'];
        $debit = $debit * $item['currency_rate'];
      }

      if($credit){
        $ledgers[Carbon::parse($item['date'])->format('Y-m-d')][] = [
          "reason" => $item['description'],
          "credit" => $credit,
          "currency" => "rmb"
        ];
        $balance += $credit;
      }
      if($debit){
        $ledgers[Carbon::parse($item['date'])->format('Y-m-d')][] = [
          "reason" => $item['description'],
          "debit" => $debit,
          "currency" => "rmb"
        ];
        $balance -= $debit;
      }

      return $item;

    });

    Item::whereIn('status', ['shipped', 'received'])->where('supplier_id', $supplier->id)
      ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)
      ->get()->map(function ($item) use(&$balance, &$ledgers){
        $ledgers[$item->check_date][] = [
          "reason" => $item->item,
          "credit" => $item->total,
          "currency" => "rmb"
        ];
        $balance += $item->total;
      });

    $title = "Supplier Statement";
    $description = "Supplier Statement Page";
    return view('suppliers.statement', compact('supplier', 'title', 'balance', 'description', 'ledgers'));
  }
}
