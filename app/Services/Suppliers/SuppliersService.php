<?php

namespace App\Services\Suppliers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SuppliersService
{

  public function store($request)
  {
    $request->validate([
      'name' => 'nullable',
      'code' => 'nullable|unique:suppliers,code',
      'phone' => 'nullable|unique:suppliers',
      'store_number' => 'required'
    ]);
    Supplier::create([
      'name' => $request->name,
      'code' => $request->code,
      'phone' => $request->phone,
      'store_number' => $request->store_number
    ]);
  }
}
