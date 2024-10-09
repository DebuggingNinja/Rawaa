<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Container;
use App\Models\Item;
use App\Models\Order;
use App\Models\Repository;
use App\Services\Financials\BalanceCalculator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CurrentReopositoriesController extends Controller
{
  public function index()
  {
    $title = "Repository";
    $description = "Current Items in The Repository";
    $repos = Repository::all(['id', 'name', 'code']);
    return view('repository.index', compact('title', 'description', 'repos'));
  }
  public function currentItems(Request $request)
  {
    $per_page = session('pagination_per_page');
    $per_page = (!empty($per_page)) ? $per_page : 20;
    $page     = (!empty($_GET['page'])) ? $_GET['page'] : 1;
    $repo = Repository::with('orders.items')->findOrFail($request->repo_id);
    if($request->search){
      $orders = $repo->orders()->where(function ($query) use($request){
          $query->whereHas('client', function ($query) use($request){
            $query->where('name', 'LIKE', "%$request->search%")
              ->orWhere('code', $request->search);
          })->orWhereHas('items', function ($query) use($request){
            $query->where('item', 'LIKE', "%$request->search%");
          });
      });
      if($request->showShipped) {
        $orders->whereHas('items', function ($query) use($request){
          if ($request->start_date) $query->whereDate('shipping_date', '>=', $request->start_date);
          if ($request->end_date) $query->whereDate('shipping_date', '<=', $request->end_date);
        });
      }
      $orders = $orders->get();
    }else{
      if($request->showShipped){
        $orders = $repo->orders()->whereHas('items', function ($query) use($request){
          if($request->start_date) $query->whereDate('shipping_date', '>=', $request->start_date);
          if($request->end_date) $query->whereDate('shipping_date', '<=', $request->end_date);
        })->get();
      }else $orders = $repo->orders;
    }
    $itemsWithStatusReceived = $orders->flatMap(function ($order) use($request){
      return $request?->showShipped ? $order->items->where('status', 'shipped') :
        $order->items->where('status', 'received');
    });
    // I had to make my own paginator because Laravel's paginate method doesn't work on collections
    $currentPageItems = $itemsWithStatusReceived->slice(($page - 1) * $per_page, $per_page)->all();
    $items = new LengthAwarePaginator(
      $currentPageItems,
      count($itemsWithStatusReceived),
      $per_page,
      $page,
      ['path' => url()->current()]
    );
    $containers = Container::where([
      ['number', '!=', Null],
      [function ($query) use ($request) {
        if (($term = $request->search)) {
          $query->orWhere('number', 'LIKE', '%' . $term . '%')
            ->orWhere('lock_number', 'LIKE', '%' . $term . '%')
            ->get();
        }
      }]
    ])->get();
    $title = "Repository";
    $description = "Current Items in The Repository";
    $repos = Repository::all(['id', 'name', 'code']);
    return view('repository.currentItems', compact('title', 'description', 'items', 'repo', 'repos', 'containers'));
  }

  public function ship(Request $request){
    $request->validate([
      'container' => 'required|exists:containers,id',
      'items' => 'required|array',
      'items.*' => 'required|exists:items,id'
    ]);
    $container = Container::find($request->container)->number;
    $item = Item::whereIn('id', $request->items)->update([
      'container_number' => $container,
      'shipping_date' => now(),
      'status' => 'shipped'
    ]);
    Client::with([])->whereHas('orders', fn($q) => $q->whereHas('items', fn($sq) => $sq->whereIn('id', $request->items)))
      ->get()->map(function ($c){
        BalanceCalculator::Client($c->id);
      });
    if(!$item) return response()->json(['error' => true, 'message' => 'failed to ship items']);
    return response()->json(['error' => false, 'message' => 'items shipped']);
  }
}
