<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Container;
use App\Models\ContainerClient;
use App\Models\ContainerItem;
use App\Models\Item;
use App\Models\Repository;
use App\Models\ShippingCompany;
use App\Services\Financials\BalanceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContainersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('can:view containers')->only('index');
    $this->middleware('can:add containers')->only('create', 'store');
    $this->middleware('can:update containers')->only('update', 'edit');
    $this->middleware('can:delete containers')->only('destroy');
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
    $containers = Container::with(['company', 'broker', 'repo'])->where([
      ['number', '!=', Null],
      [function ($query) use ($request) {
        if (($term = $request->search)) {
          $query->orWhere('number', 'LIKE', '%' . $term . '%')
            ->orWhere('lock_number', 'LIKE', '%' . $term . '%')
            ->get();
        }
      }]
    ])->paginate($per_page);
    $title = "Containers";
    $description = "Show Containers";
    return view('containers.index', compact('title', 'description', 'containers'));
  }
  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $title = "Create new Container";
    $description = "Create New Container Page";
    $brokers = Broker::all(['id', 'name', 'code']);
    $repos = Repository::all(['id', 'name', 'code']);
    $companies = ShippingCompany::all(['id', 'name']);
    return view('containers.create', compact('title', 'description', 'repos', 'companies', 'brokers'));
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
      'number' => 'required|unique:containers',
      'lock_number' => 'required',
      'est_arrive_date' => 'required',
      'destination' => 'required',
      'shipping_type' => 'required|in:complete,partial',
      'company' => 'required|exists:shipping_companies,id',
      'broker' => 'required|exists:brokers,id',
      'repo' => 'required|exists:repositories,id',
      'cost_dollar' => 'sometimes|numeric',
      'cost_rmb' => 'sometimes|numeric'
    ]);
    $container = Container::create([
      'number' => $request->number,
      'lock_number' => $request->lock_number,
      'est_arrive_date' => $request->est_arrive_date,
      'destination' => $request->destination,
      'shipping_type' => $request->shipping_type,
      'shipping_company_id' => $request->company,
      'broker_id' => $request->broker,
      'repository_id' => $request->repo,
      'cost_dollar' => $request->cost_dollar,
      'cost_rmb' => $request->cost_rmb
    ]);
    $container->generateSerial();
    BalanceCalculator::company($request->company);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Container Created Successfully');
    } else {
      //toastr()->success('تم إنشاء الحاوية بنجاح');
    }
    return redirect()->route('containers.edit', $container)->with('create', 'Container Created Successfully');
  }
  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $title         = 'Edit Container';
    $description   = 'Edit Container Page';
    $container = Container::findOrFail($id);
    if(!$container->serial_number) $container->generateSerial();
    $brokers = Broker::all(['id', 'name', 'code']);
    $repos = Repository::all(['id', 'name']);
    $companies = ShippingCompany::all(['id', 'name']);
    $collectedItems = Item::with('containerItem')->where('container_number', $container->number)->get();
    $items = [];
    foreach ($collectedItems as $item){
        if(!isset($items[$item->order->client_id])){
          $cl = $container->clients->where('client_id', $item->order->client_id)->first();
          if(!$cl) $cl = $container->clients()->create([
              'client_id' => $item->order->client_id
          ]);
          $items[$item->order->client_id] = (object) [
            "client" => $item->order->client,
            "item" => $cl,
            "weight" => 0,
            "cbm" => 0,
            "quantity" => 0
          ];
        }
      $items[$item->order->client_id]->weight += $item->weight;
      $items[$item->order->client_id]->cbm += $item->cbm;
      $items[$item->order->client_id]->quantity += $item->carton_quantity;
    }
    return view('containers.edit', compact('title', 'description', 'container', 'brokers', 'repos', 'companies', 'items'));
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
      'number' => 'required|unique:containers,id,' . $id,
      'lock_number' => 'required',
      'est_arrive_date' => 'required|date',
      'destination' => 'required',
      'shipping_type' => 'required|in:complete,partial',
      'company' => 'required|exists:shipping_companies,id',
      'broker' => 'required|exists:brokers,id',
      'repo' => 'required|exists:repositories,id',
      'cost_dollar' => 'sometimes|numeric',
      'cost_rmb' => 'sometimes|numeric'
    ]);
    $container = Container::findOrFail($id);
    $container->update([
      'name' => $request->number,
      'lock_number' => $request->lock_number,
      'est_arrive_date' => $request->est_arrive_date,
      'destination' => $request->destination,
      'shipping_type' => $request->shipping_type,
      'shipping_company_id' => $request->company,
      'broker_id' => $request->broker,
      'repository_id' => $request->repo,
      'cost_dollar' => $request->cost_dollar,
      'cost_rmb' => $request->cost_rmb
    ]);
    BalanceCalculator::company($request->company);
    if (app()->getLocale() == 'en') {
      //toastr()->success('Container Updated Successfully');
    } else {
      //toastr()->success('تم تحديث الحاوية بنجاح');
    }
    return redirect()->route('containers.index')->with('update', 'Container Updated Successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $container = Container::findOrFail($id);
    $container->delete();
    if (app()->getLocale() == 'en') {
      //toastr()->success('Container Deleted Successfully');
    } else {
      //toastr()->success('تم حذف الحاوية بنجاح');
    }
    return redirect()->route('containers.index')->with('delete', 'Container Deleted Successfully');
  }

  public function items(Request $request)
  {
    $this->validate($request, [
      'item_id' => 'required|exists:items,id',
      'container_id' => 'required|exists:containers,id',
      'meter_price' => 'required|numeric',
      'quantity' => 'required|numeric',
      'total' => 'required|numeric',
      'notes' => 'required',
    ]);
    $item = ContainerClient::updateOrCreate([
      'id' => $request->item_id,
    ], [
      'container_id' => $request->container_id,
      'meter_price' => $request->meter_price,
      'collect_by' => $request?->{"collect_by-" . $request->item_id} ?? "cbm",
      'total' => $request->total,
      'quantity' => $request->quantity,
      'notes' => $request->notes
    ]);

    BalanceCalculator::Client($item->client_id);

    if (app()->getLocale() == 'en') {
      return response()->json(['success' => 'Item Saved Successfully', 'data' => $item]);
    } else {
      return response()->json(['success' => 'تم حفظ البند بنجاح', 'data' => $item]);
    }
  }
  public function itemsAll(Request $request)
  {
    $this->validate($request, [
      'items' => 'array',
    ]);
    $container = null;
    foreach ($request->items as $i){
      $itemArray = json_decode($i, true);
      Validator::make($itemArray, [
        'container_id' => 'required|integer',
        'item_id' => 'required|integer',
        'meter_price' => 'nullable|numeric',
        'quantity' => 'nullable|numeric',
        'total' => 'nullable|numeric',
        'notes' => 'nullable|string',
      ]);
      $item = ContainerClient::updateOrCreate([
        'id' => $itemArray['item_id'],
      ], [
        'container_id' => $itemArray['container_id'],
        'meter_price' => (float) $itemArray['meter_price'],
        'total' => (float) $itemArray['total'],
        'notes' => $itemArray['notes'],
        'quantity' => $itemArray['quantity'],
        'collect_by' => $itemArray["collect_by-" . $itemArray['item_id']] ?? "cbm",
      ]);
      BalanceCalculator::Client($item->client_id);
    }
    if (app()->getLocale() == 'en') {
      return response()->json(['success' => 'Item Saved Successfully']);
    } else {
      return response()->json(['success' => 'تم حفظ البند بنجاح']);
    }
  }
}
