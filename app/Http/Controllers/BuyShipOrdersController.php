<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\KeyGenerator;
use App\Models\Order;
use App\Models\Repository;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

use function PHPSTORM_META\type;

class BuyShipOrdersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('can:view buy ship orders')->only('index');
    $this->middleware('can:add buy ship orders')->only('create', 'store');
    $this->middleware('can:update buy ship orders')->only('update', 'edit');
    $this->middleware('can:delete buy ship orders')->only('destroy');
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
    /*$orders = BuyShipOrder::where([
            ['id','!=',Null],
            [function($query) use ($request){
                if(($term = $request->search)){
                    $query->orWhere('client.name','LIKE','%' . $term . '%')
                    ->get();
                }
            }]
        ])->paginate($per_page);
*/
    $orders = Order::whereHas('client', function (Builder $query) use ($request) {
      if (($term = $request->search)) {
        $query->where('name', 'LIKE', '%' . $term . '%')
          ->orWhere('code', 'LIKE', '%' . $term . '%');;
      }
      $query->where('type', 'buy_ship');
    })->orWhereHas('repo', function (Builder $query) use ($request) {
      if (($term = $request->search)) {
        $query->where('name', 'LIKE', '%' . $term . '%')
          ->orWhere('code', 'LIKE', '%' . $term . '%');
      }
      $query->where('type', 'buy_ship');
    })->where('type', 'buy_ship')->latest()->paginate($per_page);
    $title = "Buy And Ship Orders";
    $description = "Show Orders";
    return view('buy_ship_orders.index', compact('title', 'description', 'orders'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $title = "Create New Buy And Ship Order";
    $description = "Create New Buy And Ship Order Page";
    $clients = Client::all(['id', 'name', 'code']);
    $repos = Repository::all(['id', 'name', 'code']);
    $countRows = Order::count();

    $file = $countRows > 0 ? ceil($countRows / 30) : 1;

    return view('buy_ship_orders.create', compact('title', 'description', 'repos', 'clients', 'file'));
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
      'repo' => 'required',
      'client' => 'required',
//      'file' => 'required|numeric',
//      'registery' => 'required|numeric',
//      'paper' => 'required|numeric',
    ]);
    $order = Order::create([
//      'code'  => KeyGenerator::Code('buy_ship_order'),
      'repository_id' => $request->repo,
      'client_id' => $request->client,
//      'file' => $request->file,
//      'registery' => $request->registery,
//      'paper' => $request->paper,
      'type' => 'buy_ship'
    ]);
//    if (app()->getLocale() == 'en') {
//      //toastr()->success('Order Created Successfully');
//    } else {
//      //toastr()->success('تم إنشاء الطلب بنجاح');
//    }

    $order->generateBuyShipSerial();
    return redirect()->route('buy_ship_orders.edit', $order->id)->with('create', 'Buy Ship Order Created Successfully');
  }

  public function setCommission(Request $request)
  {
    $request->validate([
      'order' => 'required|exists:orders,id',
      'commission' => 'required|numeric|min:1|max:100',
    ]);
    $order = Order::findOrFail($request->order)->update([
      'commission' => $request->commission
    ]);
    if(!$order) return response()->json(['error' => true, 'message' => 'failed to ship items']);
    return response()->json(['error' => false, 'message' => 'items shipped']);
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
    $title         = 'Edit Buy Ship Order';
    $description   = 'Edit Buy Ship Order Page';
    $order = Order::findOrFail($id);
    $clients = Client::all();
    $repos = Repository::all();
    $suppliers = Supplier::all();
    return view('buy_ship_orders.edit', compact('title', 'description', 'order', 'repos', 'clients', 'suppliers'));
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
      'repo' => 'required',
      'client' => 'required',
//      'file' => 'required',
//      'registery' => 'required',
//      'paper' => 'required',
    ]);
    $order = Order::findOrFail($id);
    $order->update([
      'repository_id' => $request->repo,
      'client_id' => $request->client,
//      'file' => $request->file,
//      'registery' => $request->registery,
//      'paper' => $request->paper,
    ]);
    if(!$order->code) $order->generateBuyShipSerial();
    if (app()->getLocale() == 'en') {
      //toastr()->success('Order Updated Successfully');
    } else {
      //toastr()->success('تم تحديث الطلب بنجاح');
    }
    return redirect()->route('buy_ship_orders.edit', $order->id)->with('update', 'Buy Ship Order Updated Successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $find_order = Order::findOrFail($id);
    $find_order->delete();
    if (app()->getLocale() == 'en') {
      //toastr()->success('Order Deleted Successfully');
    } else {
      //toastr()->success('تم حذف الطلب بنجاح');
    }
    return redirect()->route('buy_ship_orders.index')->with('delete', 'Buy And Ship Order Deleted Successfully');
  }
}
