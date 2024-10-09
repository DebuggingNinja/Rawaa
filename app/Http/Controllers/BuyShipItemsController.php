<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Supplier;
use App\Services\Financials\BalanceCalculator;
use Illuminate\Http\Request;


class BuyShipItemsController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    //
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $request->validate(['carton_code' => 'required_if:order.type,buy_ship',
      'item' => 'required_if:order.type,buy_ship',
      'carton_quantity' => 'required|numeric',
      'pieces_number' => 'required_if:order.type,buy_ship|numeric',
      'single_price' => 'required_if:order.type,buy_ship|numeric',
      'check_date' => 'required_if:status,requested|required_if:order.type,buy_ship',
      'cbm' => 'required|numeric',
      'weight' => 'required|numeric',
      'status' => 'required_unless:id,null',
      'supplier_id' => 'required_if:order.type,buy_ship|exists:suppliers,id',
      'check_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
      'container_number' => 'required_if:status,shipped',
      'check_notes' => 'required_if:status,requested',
      'receive_notes' => 'required_if:status,checked',
      'cancelled_notes' => 'required_if:status,cancelled',
      'order_id' => 'required',
      'receive_date' => 'required_if:status,waiting',
    ]);
    $cbm = $request->cbm;
    $weight = $request->weight;
    if(!$request?->id){
      $cbm = $request->cbm * $request->carton_quantity;
      $weight = $request->weight * $request->carton_quantity;
    }
    $imageName = null;
    if ($request->hasFile('check_image')) {
      $image = $request->file('check_image');
      $imageName = $request->id . '_' . time() . '.' . $image->getClientOriginalExtension();
      $image->storeAs('public/check_images', $imageName);
    }
    $receiveDate = ($request->receive_date == null && $request->status == 'received') ? now() : null;
    if($request->status == 'waiting') $receiveDate = $request->receive_date;
    $item = Item::updateOrCreate([
      'id' => $request->id
    ], [
      'carton_code' => $request->carton_code,
      'item' => $request->item,
      'carton_quantity' => $request->carton_quantity,
      'pieces_number' => $request->pieces_number,
      'single_price' => $request->single_price,
      'total' =>  $request->single_price * ($request->pieces_number * $request->carton_quantity),
      'check_date' => $request->check_date,
      'cbm' => $cbm,
      'weight' => $weight,
      'status' => $request->status ?? "requested",
      'supplier_id' => $request->supplier_id,
      'check_image' => $imageName,
      'container_number' => $request->container_number,
      'check_notes' => $request->check_notes,
      'receive_notes' => $request->receive_notes,
      'cancelled_notes' => $request->cancelled_notes,
      'order_id' => $request->order_id,
      'receive_date' => $receiveDate,
      'mark' => $request?->mark,
    ]);
    $item_ = $item instanceof Item ? $item : Item::with(['order'])->find($request->id ?? $item);
    if($item_){
      BalanceCalculator::Supplier($item_?->supplier_id);
      BalanceCalculator::Client($item_?->order?->client_id);
    }
    if (app()->getLocale() == 'en') {
      return response()->json(['success' => 'Item Saved Successfully', 'data' => $item]);
    } else {
      return response()->json(['success' => 'تم حفظ البند بنجاح', 'data' => $item]);
    }
  }

  public function update_bulk(Request $request)
  {
    $request->validate([
      'items' => 'required|array',
      'items.*' => 'required|exists:items,id',
      'status' => 'required',
      'check_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
      'check_notes' => 'sometimes',
      'receive_notes' => 'sometimes',
      'cancelled_notes' => 'sometimes',
      'receive_date' => 'required_if:status,waiting',
      'check_date' => 'required_if:status,requested',
    ]);
    $imageName = null;
    if ($request->hasFile('check_image')) {
      $image = $request->file('check_image');
      $imageName = $request->id . '_' . time() . '.' . $image->getClientOriginalExtension();
      $image->storeAs('public/check_images', $imageName);
    }
    $receiveDate = ($request->receive_date == null && $request->status == 'received') ? now() : null;
    if($request->status == 'waiting') $receiveDate = $request->receive_date;
    $item = Item::whereIn('id', $request->items);
    $update = $item->update([
      'status' => $request->status,
      'check_image' => $imageName,
      'check_notes' => $request->check_notes,
      'receive_notes' => $request->receive_notes,
      'cancelled_notes' => $request->cancelled_notes,
      'receive_date' => $receiveDate,
      'check_date' => $request->check_date,
    ]);

    Supplier::with([])->whereHas('items', fn($q) => $q->whereIn('id', $request->items))
      ->get()->map(function ($supplier){
      BalanceCalculator::Supplier($supplier->id);
    });
    BalanceCalculator::Client($item->first()?->order?->client_id);
    if (app()->getLocale() == 'en') {
      return response()->json(['success' => 'Items Updated Successfully', 'data' => $item]);
    } else {
      return response()->json(['success' => 'تم تعديل البنود بنجاح', 'data' => $item]);
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
    //
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
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $items = Item::find($id);
    if ($items) {
      $items->delete();
    }
    if (app()->getLocale() == 'en') {
      return response()->json(['success' => 'Item Deleted Successfully']);
    } else {
      return response()->json(['success' => 'تم حذف البند بنجاح']);
    }
  }

  public function destroy_bulk(Request $request)
  {
    $items = Item::whereIn('id', $request->items);
    if ($items) {
      $items->delete();
    }
    if (app()->getLocale() == 'en') {
      return response()->json(['success' => 'Item Deleted Successfully']);
    } else {
      return response()->json(['success' => 'تم حذف البند بنجاح']);
    }
  }
}
