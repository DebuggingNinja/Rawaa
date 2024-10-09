<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\ContainerItem;
use App\Models\Item;
use App\Models\ShippingCompany;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShippingCompaniesController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('can:view shipping companies')->only('index');
    $this->middleware('can:add shipping companies')->only('create', 'store');
    $this->middleware('can:update shipping companies')->only('update', 'edit');
    $this->middleware('can:delete shipping companies')->only('destroy');
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
    $companies = ShippingCompany::where([
      ['name', '!=', Null],
      [function ($query) use ($request) {
        if (($term = $request->search)) {
          $query->orWhere('name', 'LIKE', '%' . $term . '%')
            ->orWhere('email', 'LIKE', '%' . $term . '%')
            ->orWhere('phone', 'LIKE', '%' . $term . '%')
            ->orWhere('phone2', 'LIKE', '%' . $term . '%')
            ->orWhere('code', 'LIKE', '%' . $term . '%')
            ->get();
        }
      }]
    ])->paginate($per_page);
    $title = "Shipping Companies";
    $description = "Show Shipping Companies";
    return view('shipping_companies.index', compact('title', 'description', 'companies'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $title = "Create new Company";
    $description = "Create New Shipping Company Page";
    return view('shipping_companies.create', compact('title', 'description'));
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
      'code' => 'required|unique:shipping_companies,code',
      'email' => 'email|unique:shipping_companies|nullable',
      'phone' => 'nullable|unique:shipping_companies',
      'phone2' => 'nullable|unique:shipping_companies',
      'address' => 'nullable',
    ]);
    ShippingCompany::create([
      'name' => $request->name,
      'code' => $request->code,
      'email' => $request->email,
      'phone' => $request->phone,
      'phone2' => $request->phone2,
      'address' => $request->address,
    ]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Company Created Successfully');
    } else {
      //toastr()->success('تم إنشاء الشركة بنجاح');
    }
    return redirect()->route('shipping_companies.index',)->with('create', 'Company Created Successfully');
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

  public function statement(Request $request, $id)
  {

    $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::parse("- 1 month");
    $end = $request->end_time ? Carbon::parse($request->end_time) : now();

    $company = ShippingCompany::with(['ledgers' => function ($query) use($start, $end) {
      $query->whereIn('action', ['credit', 'debit']);
      $query->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
      $query->orderBy('date');
    }])->findOrFail($id);

    $balance = $dollar_balance = 0;
    $ledgers = [];
    $company->ledgers->map(function ($item) use(&$balance, &$ledgers){
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

    Container::where('shipping_company_id', $company->id)
      ->whereDate('shipping_date', '>=', $start)->whereDate('shipping_date', '<=', $end)
      ->get()->map(function ($item) use(&$balance, &$dollar_balance, &$ledgers){
        $ledgers[$item->shipping_date][] = [
          "reason" => trans('company.shipping_for') . " " . $item->number . "({$item->serial_number})",
          "credit" => $item->cost_rmb,
          "credit_dollar" => $item->cost_dollar,
          "currency" => "rmb"
        ];
        $balance += $item->cost_rmb;
        $dollar_balance += $item->cost_dollar;
      });

    $title = "Shipping Company Statement";
    $description = "Shipping Company Statement Page";
    return view('shipping_companies.statement', compact('company', 'title', 'balance', 'dollar_balance', 'description', 'ledgers'));
  }
  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $title         = 'Edit Company';
    $description   = 'Edit Company Page';
    $company = ShippingCompany::findOrFail($id);
    return view('shipping_companies.edit', compact('title', 'description', 'company'));
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
      'code' => 'required|unique:shipping_companies,code,' . $id,
      'email' => 'nullable|email|unique:shipping_companies,email,' . $id,
      'phone' => 'nullable|unique:shipping_companies,phone,' . $id,
      'phone2' => 'nullable|unique:shipping_companies,phone2,' . $id,
      'address' => 'nullable',
    ]);
    $company = ShippingCompany::findOrFail($id);
    $company->update([
      'name' => $request->name,
      'code' => $request->code,
      'email' => $request->email,
      'phone' => $request->phone,
      'phone2' => $request->phone2,
      'address' => $request->address,
    ]);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Company Updated Successfully');
    } else {
      //toastr()->success('تم تحديث الشركة بنجاح');
    }
    return redirect()->route('shipping_companies.index')->with('update', 'Company Updated Successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $find_company = ShippingCompany::findOrFail($id);
    $find_company->delete();
    if (app()->getLocale() == 'en') {
      //toastr()->success('Company Deleted Successfully');
    } else {
      //toastr()->success('تم حذف الشركة بنجاح');
    }
    return redirect()->route('shipping_companies.index')->with('delete', 'Company Deleted Successfully');
  }
}
