@section('title', $title)
@section('description', $description)
@section('style')
  <style>
    .hide{
      display: none;
    }
    .modal-lg{
      max-width: 1250px !important;
      width: 100%;
    }
    .product-row{
      flex-wrap: wrap;
    }
    .check-image{
      margin-top: 20px;
    }
    .image-modal{
      padding: 0;
      position: relative;
    }
    .image-modal .btn-close{
      position: absolute;
      top: 10px;
      right: 10px;
    }
    .int_num{
      width: fit-content;
      display: inline-block;
      position: absolute;
      top: 12px;
      left: 50px;
      font-size: 24px;
      color: #000;
    }
    .badge{
      border-radius: 5px;
      height: 28px;
      line-height: 28px;
      font-size: 14px;
    }
    .badge-requested{
      background-color: var(--bg-warning)
    }
    .badge-checked{
      background-color: var(--bg-info)
    }
    .badge-waiting{
      background-color: var(--bg-danger)
    }
    .badge-received{
      background-color: var(--bg-success)
    }
    .badge-shipped{
      background-color: var(--bg-dark);
    }
    .badge-cancelled{
      background-color: var(--bg-primary)
    }
  </style>
@endsection
@extends('layout.app')
@section('content')
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="d-flex align-items-center user-member__title mb-30 mt-30">
          <h4 class="text-capitalize">{{ trans('order.update-order') }}</h4>
        </div>
      </div>
    </div>
    <div class="card mb-50">
      <div class="row p-5">
        <div class="col-12">
          <form action="{{ route('buy_ship_orders.update', $order->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('put')
            <div class="row">
              <div class="col-12 col-md-6">
                <h6 class="mt-3">{{ trans('id') }} : <span dir="ltr">{{ $order->code }}</span></h6>
              </div>
              <div class="col-12 col-md-6">
                <h6 class="mt-3">{{ trans('created_at') }} : {{ date('d-m-Y', strtotime($order->created_at)) }}</h6>
              </div>
              <div class="col-12">
                <br>
              </div>
              <div class="col-12 col-md-4">
                <div class="form-group mb-25">
                  <label for="client" class="color-dark fs-14 fw-500 align-center">
                    {{ trans('client.clients') }}
                    <span class="text-danger">*</span>
                  </label>
                  <select class="form-select" name="client" id="client">
                    <option value="">---</option>
                    @foreach ($clients as $client)
                      <option value="{{ $client->id }}" {{ $order->client_id == $client->id ? 'selected' : '' }}>
                        {{ $client->name }} - {{ $client->code }}</option>
                    @endforeach
                  </select>
                  @if ($errors->has('client'))
                    <p class="text-danger">{{ $errors->first('client') }}</p>
                  @endif
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="form-group mb-25">
                  <label for="repo" class="color-dark fs-14 fw-500 align-center">
                    {{ trans('repo.repos') }}
                    <span class="text-danger">*</span>
                  </label>
                  <select class="form-select " name="repo" id="repo">
                    <option value="">---</option>
                    @foreach ($repos as $repo)
                      <option value="{{ $repo->id }}" {{ $order->repo->id == $repo->id ? 'selected' : '' }}>
                        {{$repo->name . ' - ' . $repo->code}}</option>
                    @endforeach
                  </select>
                  @if ($errors->has('repo'))
                    <p class="text-danger">{{ $errors->first('repo') }}</p>
                  @endif
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="button-group d-flex pt-25 justify-content-md-end justify-content-stretch">
                  <button type="submit"
                          class="btn btn-primary btn-default btn-squared radius-md shadow2 btn-sm">Submit</button>
                </div>

              </div>
            {{--            <div class="col-12 col-md-3">--}}
            {{--              <div class="form-group mb-25">--}}
            {{--                <label for="registery" class="color-dark fs-14 fw-500 align-center">{{ trans('order.registery') }}--}}
            {{--                  <span class="text-danger">*</span></label>--}}
            {{--                <input type="text" class="form-control ih-medium ip-gray radius-xs b-light px-15" name="registery"--}}
            {{--                  id="registery" value="{{ $order->registery }}">--}}
            {{--                @if ($errors->has('registery'))--}}
            {{--                <p class="text-danger">{{ $errors->first('registery') }}</p>--}}
            {{--                @endif--}}
            {{--              </div>--}}
            {{--            </div>--}}
            {{--            <div class="col-12 col-md-3">--}}
            {{--              <div class="form-group mb-25">--}}
            {{--                <label for="paper" class="color-dark fs-14 fw-500 align-center">{{ trans('order.paper') }}--}}
            {{--                  <span class="text-danger">*</span>--}}
            {{--                </label>--}}
            {{--                <input type="text" class="form-control ih-medium ip-gray radius-xs b-light px-15" name="paper"--}}
            {{--                  id="paper" value="{{ $order->paper }}">--}}
            {{--                @if ($errors->has('paper'))--}}
            {{--                <p class="text-danger">{{ $errors->first('paper') }}</p>--}}
            {{--                @endif--}}
            {{--              </div>--}}
            {{--            </div>--}}
            {{--            <div class="button-group d-flex pt-25 justify-content-md-end justify-content-stretch">--}}
            {{--              <button type="submit"--}}
            {{--                class="btn btn-primary btn-default btn-squared radius-md shadow2 btn-sm">Submit</button>--}}
            {{--            </div>--}}

            {{--          </div>--}}
            </div>
          </form>
        </div>
      </div>
    </div>

    @can('add buy ship items')
      <div class="card">
        <div class="card-header">
          {{ trans('order.add-item') }}
        </div>
        <div class="card-body">
          <form class="save-item-form" method="post" action="{{ route('buy_ship_items.store') }}"
                enctype="multipart/form-data">
            <div class="row">
              <div class="col-md-3">
                @csrf
                <input type="hidden" name="order" value="{{$order->id}}">
                <label>{{ trans('order.supplier') }}</label>
                <select class="select2 dynamic-select supplier_select" name="supplier_id"
                        style="width: 100px">
                  <option>select supplier</option>
                  @foreach ($suppliers as $supplier)
                    <option data-store-number="{{ $supplier->store_number }}" data-phone="{{ $supplier->phone }}"
                            value="{{$supplier->id}}">{{$supplier->name}} - {{$supplier->code}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <input name="order_id" type="hidden" value="{{ $order->id }}">
                <label for="carton_code">{{ trans('order.carton_code') }}</label>
                <input id="carton_code" name="carton_code" type="text" class="form-control">
              </div>
              <div class="col-md-3">
                <label for="item">{{ trans('order.item') }}</label>
                <input id="item" name="item" type="text" class="form-control">
              </div>
              <div class="col-md-3">
                <label for="check_notes">{{ trans('order.check_notes') }}</label>
                <textarea id="check_notes" class="check_notes form-control"
                          name="check_notes" cols="20" style="min-width:100px"></textarea>
              </div>
              <div class="col-md-2">
                <label for="carton_quantity">{{ trans('order.carton_quantity') }}</label>
                <input id="carton_quantity" name="carton_quantity" type="number" class="form-control">
              </div>
              <div class="col-md-2">
                <label for="pieces_number">{{ trans('order.pieces_number') }}</label>
                <input id="pieces_number" name="pieces_number" type="number" class="form-control">
              </div>
              <div class="col-md-2">
                <label for="single_price">{{ trans('order.single_price') }}</label>
                <input id="single_price" name="single_price" type="text" class="form-control">
              </div>

              <div class="col-md-2">
                <label for="cbm">{{ trans('order.cbm') }}</label>
                <input name="cbm" id="cbm" type="text" class="form-control">
              </div>
              <div class="col-md-2">
                <label for="weight">{{ trans('order.weight') }}</label>
                <input id="weight" name="weight" type="text" class="form-control">
              </div>
              <div class="col-md-2">
                <label for="check_date">{{ trans('order.check_date') }}</label>
                <input id="check_date" name="check_date" type="date" class="form-control">
              </div>
              <div class="col-md-12 flex-container">
                <br>
                <br>
                <button type="submit" class="btn btn-primary">
                  {{ trans('order.add-item') }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    @endcan
    @can('view buy ship items')
      <div class="row">
        <div class="col-lg-12">
          <div class="d-flex align-items-center justify-content-between user-member__title mb-30 mt-30">
            <h4 class="text-capitalize">{{ trans('order.items') }}</h4>
            <div class="d-flex align-items-center justify-content-end gap-2">
              <button id="print-items" class="btn btn-primary">print</button>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="row mb-3" id="check-columns">
            <div class="col-12">
              <div class="d-flex gap-2 flex-wrap">
                <div class="form-check">
                  <input id="c-item" type="checkbox" class="form-check-input select-effector" data-effect=".item" checked>
                  <label for="c-item" class="form-check-label">{{ trans('order.item') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-carton_quantity" type="checkbox" class="form-check-input select-effector" data-effect=".carton_quantity" checked>
                  <label for="c-carton_quantity" class="form-check-label">{{ trans('order.carton_quantity') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-pieces_number" type="checkbox" class="form-check-input select-effector" data-effect=".pieces_number" checked>
                  <label for="c-pieces_number" class="form-check-label">{{ trans('order.pieces_number') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-single_price" type="checkbox" class="form-check-input select-effector" data-effect=".single_price" checked>
                  <label for="c-single_price" class="form-check-label">{{ trans('order.single_price') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-total" type="checkbox" class="form-check-input select-effector" data-effect=".total" checked>
                  <label for="c-total" class="form-check-label">{{ trans('order.total') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-receive_date" type="checkbox" class="form-check-input select-effector" data-effect=".receive_date">
                  <label for="c-receive_date" class="form-check-label">{{ trans('order.receive_date') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-cbm" type="checkbox" class="form-check-input select-effector" data-effect=".cbm" checked>
                  <label for="c-cbm" class="form-check-label">{{ trans('order.cbm') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-weight" type="checkbox" class="form-check-input select-effector" data-effect=".weight" checked>
                  <label for="c-weight" class="form-check-label">{{ trans('order.weight') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-status" type="checkbox" class="form-check-input select-effector" data-effect=".status" checked>
                  <label for="c-status" class="form-check-label">{{ trans('order.status') }}</label>
                </div>
                <div class="form-check">
                  <input id="supplier" type="checkbox" class="form-check-input select-effector" data-effect=".supplier" checked>
                  <label for="supplier" class="form-check-label">{{ trans('order.supplier') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-phone" type="checkbox" class="form-check-input select-effector" data-effect=".phone">
                  <label for="c-phone" class="form-check-label">{{ trans('order.phone') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-container_number" type="checkbox" class="form-check-input select-effector" data-effect=".container_number" checked>
                  <label for="c-container_number" class="form-check-label">{{ trans('order.container_number') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-check_date" type="checkbox" class="form-check-input select-effector" data-effect=".check_date" checked>
                  <label for="c-check_date" class="form-check-label">{{ trans('order.check_date') }}</label>
                </div>
                <div class="form-check">
                  <input id="c-check_image" type="checkbox" class="form-check-input select-effector" data-effect=".check_image">
                  <label for="c-check_image" class="form-check-label">{{ trans('order.check_image') }}</label>
                </div>
              </div>
            </div>
          </div>
          <div id="items">
            <div class="row">
              <div class="col-lg-12 mb-30">
                <div class="card print-content">
                  <div class="card-header color-dark fw-500">
                    <div>
                      {{trans('order.items')}}
                    </div>
                    <div style="display: flex; gap: 10px" class="no-print">
                      <button class="btn btn-sm btn-primary update-item">
                        {{trans('order.update-item')}}
                      </button>
                      <button class="btn btn-sm btn-danger delete-item-bulk">
                        {{trans('order.delete-item')}}
                      </button>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="userDatatable global-shadow border-light-0">
                      <div class="table-responsive table-responsive-md">
                        <table class="table table-striped w-100" id="items-table">
                          <thead>
                          <tr class="userDatatable-header">
                            <th class="no-print">
                              <div class="custom-checkbox">
                                <input type="checkbox" id="check-all-items">
                                <label for="check-all-items">
                                </label>
                              </div>
                            </th>
                            <th class="item">
                              <span class="userDatatable-title">{{ trans('order.item') }}</span>
                            </th>
                            <th class="carton_quantity">
                              <span class="userDatatable-title">{{ trans('order.carton_quantity') }}</span>
                            </th>
                            <th class="pieces_number">
                              <span class="userDatatable-title">{{ trans('order.pieces_number') }}</span>
                            </th>
                            <th class="single_price">
                              <span class="userDatatable-title">{{ trans('order.single_price') }}</span>
                            </th>
                            <th class="total">
                              <span class="userDatatable-title">{{ trans('order.total') }}</span>
                            </th>
                            <th class="receive_date">
                              <span class="userDatatable-title">{{ trans('order.receive_date') }}</span>
                            </th>
                            <th class="cbm">
                              <span class="userDatatable-title">{{ trans('order.cbm') }}</span>
                            </th>
                            <th class="weight">
                              <span class="userDatatable-title">{{ trans('order.weight') }}</span>
                            </th>
                            <th class="status">
                              <span class="userDatatable-title">{{ trans('order.status') }}</span>
                            </th>
                            <th class="supplier">
                              <span class="userDatatable-title">{{ trans('order.supplier') }}</span>
                            </th>
                            <th class="phone">
                              <span class="userDatatable-title">{{ trans('order.phone') }}</span>
                            </th>
                            <th class="container_number">
                              <span class="userDatatable-title">{{ trans('order.container_number') }}</span>
                            </th>
                            <th class="check_date">
                              <span class="userDatatable-title">{{ trans('order.check_date') }}</span>
                            </th>
                            <th class="check_image">
                              <span class="userDatatable-title">{{ trans('order.check_image') }}</span>
                            </th>
                          </tr>
                          </thead>
                          <tbody>
                          @php($count = 1)
                          @foreach ($order->items as $item)
                            <tr class="userDatatable-header">
                              <td class="no-print">
                                <div class="custom-checkbox">
                                  <input class="check-item" type="checkbox" id="check-{{$item->id}}" data-id="{{$item->id}}">
                                  <label for="check-{{$item->id}}">
                                  </label>
                                </div>
                              </td>
                              <td class="item">
                                <span class="userDatatable-title">{{ $item->carton_code . " - " . $item->item  }}</span>
                              </td>
                              <td class="carton_quantity">
                                <span class="userDatatable-title data-quantity" data-quantity="{{ $item->carton_quantity }}">
                                  {{ $item->carton_quantity }}
                                </span>
                              </td>
                              <td class="pieces_number">
                                <span class="userDatatable-title data-pieces_number">
                                  {{ $item->pieces_number }}
                                </span>
                              </td>
                              <td class="single_price">
                                <span class="userDatatable-title">{{ $item->single_price }}</span>
                              </td>
                              <td class="total">
                                <span class="userDatatable-title data-total" data-total="{{ $item->total }}">{{ $item->total }}</span>
                              </td>
                              <td class="receive_date">
                                <span class="userDatatable-title">{{ $item->receive_date }}</span>
                              </td>
                              <td class="cbm">
                                <span class="userDatatable-title data-cbm" data-cbm="{{ $item->cbm }}">{{ $item->cbm }}</span>
                              </td>
                              <td class="weight">
                                <span class="userDatatable-title data-weight" data-weight="{{ $item->weight }}">{{ $item->weight }}</span>
                              </td>
                              <td class="status">
                                <div class="ignore badge badge-{{$item->status}}">
                                  {{trans($item->status)}}
                                </div>
                              </td>
                              <td class="supplier">
                                <span class="userDatatable-title">{{ $item->supplier?->name }}</span>
                              </td>
                              <td class="phone">
                                <span class="userDatatable-title">{{ $item->phone }}</span>
                              </td>
                              <td class="container_number">
                                <span class="userDatatable-title">{{ $item->container_number }}</span>
                              </td>
                              <td class="check_date">
                                <span class="userDatatable-title">{{ $item->check_date }}</span>
                              </td>
                              <td class="check_image">
                                @if($item->check_image)
                                  <a href="{{ url('uploads/check_images/' . $item->check_image) }}" target="_blank">
                                    <img style="height: 60px; width: auto;"
                                         src="{{ url('uploads/check_images/' . $item->check_image) }}"
                                         class="form-control" />
                                  </a>
                                @endif
                              </td>
                            </tr>
                          @endforeach
                          </tbody>
                          <tfoot>
                          <tr>
                            <td>{{ trans('order.total') }}</td>
                            <td class="item"></td>
                            <td class="carton_quantity" id="d-quantity">0</td>
                            <td class="pieces_number" id="d-pieces_number"></td>
                            <td id="" class="single_price"></td>
                            <td id="d-total" class="total">0</td>
                            <td class="receive_date"></td>
                            <td class="cbm" id="d-cbm">0</td>
                            <td class="weight" id="d-weight">0</td>
                            <td class="status"></td>
                            <td class="supplier"></td>
                            <td class="phone"></td>
                            <td class="container_number"></td>
                            <td class="check_date"></td>
                            <td class="check_image"></td>
                          </tr>
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modals">
              @foreach ($order->items as $item)
                <div class="modal single-item-modal-{{ $item->id }}" tabindex="-1" role="dialog">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <form class="items-data-form" id="create-item-form-{{ $item->id }}" enctype="multipart/form-data">
                          <div class="row">
                            <div class="col-md-3">
                              <input name="id" type="hidden" value="{{ $item->id }}">
                              <input name="order_id" type="hidden" value="{{ $order->id }}">
                              <label>{{ trans('order.carton_code') }}</label>
                              <input name="carton_code" class="carton_code form-control" type="text"
                                     value="{{ $item->carton_code }}">
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.item') }}</label>
                              <input name="item" type="text" class="form-control" value="{{ $item->item }}">
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.carton_quantity') }}</label>
                              <input name="carton_quantity" type="number" class="form-control" value="{{ $item->carton_quantity }}">
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.pieces_number') }}</label>
                              <input name="pieces_number" type="number" class="form-control" value="{{ $item->pieces_number }}">
                            </div>

                            <div class="col-12">
                              <br>
                            </div>

                            <div class="col-md-3">
                              <label>{{ trans('order.single_price') }}</label>
                              <input name="single_price" type="text" class="form-control" value="{{ $item->single_price }}">
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.total') }}</label>
                              <input name="total" type="text" class="form-control" data-status="{{$item->status}}" value="{{ $item->total }}">
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.check_date') }}</label>
                              <input id="check_date_{{ $item->id }}" name="check_date" type="date" class="form-control item-check_date"
                                     value="{{ $item->check_date }}">
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.receive_date') }}</label>
                              <input id="receive_date_{{ $item->id }}" name="receive_date" type="date" class="form-control item-receive_date"
                                     value="{{ $item->receive_date }}" {{ $item->status != 'waiting' ? 'readonly' : null }}>
                            </div>

                            <div class="col-12">
                              <br>
                            </div>

                            <div class="col-md-3">
                              <label>{{ trans('order.cbm') }}</label>
                              <input name="cbm" type="text" class="form-control" value="{{ $item->cbm }}">
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.weight') }}</label>
                              <input name="weight" type="text" class="form-control" value="{{ $item->weight }}">
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.status') }}</label>
                              @if($item->status == 'shipped')
                                <select data-item-id="{{ $item->id }}" class="item-status status form-control"
                                        name="status" id="item-status-{{ $item->id }}" readonly="">
                                  <option value="shipped" selected>shipped</option>
                                </select>
                              @else
                                <select data-item-id="{{ $item->id }}" class="item-status status form-control" name="status" id="item-status-{{ $item->id }}">
                                  <option value="requested" {{ $item->status == 'requested' ? 'selected' : '' }}>requested</option>
                                  <option value="checked" {{ $item->status == 'checked' ? 'selected' : '' }}>checked</option>
                                  <option value="waiting" {{ $item->status == 'waiting' ? 'selected' : '' }}>waiting</option>
                                  <option value="received" {{ $item->status == 'received' ? 'selected' : '' }}>received</option>
                                  <option value="cancelled" {{ $item->status == 'cancelled' ? 'selected' : '' }}>cancelled</option>
                                </select>
                              @endif
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.supplier') }}</label>
                              <select data-item-id="{{ $item->id }}" class="select2 dynamic-select supplier_select2" name="supplier_id"
                                      style="width: 100px">
                                <option>select supplier</option>
                                @foreach ($suppliers as $supplier)

                                  <option data-store-number="{{ $supplier->store_number }}" data-phone="{{ $supplier->phone }}"
                                          value="{{$supplier->id}}" {{$item->supplier_id == $supplier->id ? 'selected' :
                  ''}}>{{$supplier->name}} - {{$supplier->code}}</option>
                                @endforeach
                              </select>
                              {{-- <input name="supplier" type="text" class="form-control" value="{{ $item->supplier }}"> --}}
                            </div>

                            <div class="col-12">
                              <br>
                            </div>

                            <div class="col-md-3">
                              <label>{{ trans('order.phone') }}</label>
                              <input name="phone" id="phone_{{ $item->id }}" type="text" class="form-control"
                                     value="{{ $item->supplier->phone }}">
                            </div>
                            <div class="col-md-2">
                              <label>{{ trans('order.store_number') }}</label>
                              <input name="store_number" id="store_number_{{ $item->id }}" type="text" class="form-control"
                                     value="{{ $item->supplier->store_number }}">
                            </div>
                            <div class="col-md-2">
                              <label>{{ trans('client.mark') }}</label>
                              <input type="text" name="mark" class="form-control" value="{{ $item->mark }}">
                            </div>
                            @if ($item->check_image != null)
                              <div class="col-md-2">
                                <input name="check_image" class="item-check_image" type="file" size="1000000" accept="image/*">

                                <button type="button" class="btn btn-primary btn-sm check-image" data-bs-target="#image-viewer-{{ $item->id }}" data-bs-toggle="modal">
                                  {{ trans('order.check_image') }}
                                </button>
                                <div class="modal" id="image-viewer-{{ $item->id }}" tabindex="-1">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <div class="modal-body image-modal">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        <img src="{{ url('uploads/check_images/' . $item->check_image) }}" class="form-control" />
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            @else
                              <div class="col-md-2">
                                <label>{{ trans('order.check_image') }}</label>
                                <input name="check_image" class="item-check_image" type="file" size="1000000" accept="image/*">
                              </div>
                            @endif
                            <div class="col-md-3">
                              <label>{{ trans('order.container_number') }}</label>
                              <input data-item-id="{{ $item->id }}" id="container_number_{{ $item->id }}" class="container_number form-control item-container_number"
                                     name="container_number" type="text" {{ $item->status != 'shipped' ? 'readonly' : '' }} value="{{
              $item->container_number }}">
                            </div>

                            <div class="col-12">
                              <br>
                            </div>

                            <div class="col-md-4">
                              <label>{{ trans('order.check_notes') }}</label>
                              <textarea data-item-id="{{ $item->id }}" id="check_notes_{{ $item->id }}" class="check_notes form-control item-check_notes"
                                        name="check_notes" cols="20" {{
                $item->status != 'requested' ? 'readonly' : '' }} style="min-width:100px">{{ $item->check_notes }}</textarea>
                            </div>
                            <div class="col-md-4">
                              <label>{{ trans('order.receive_notes') }}</label>
                              <textarea data-item-id="{{ $item->id }}" id="receive_notes_{{ $item->id }}" class="receive_notes form-control item-receive_notes"
                                        name="receive_notes" cols="20" {{
                $item->status != 'checked' ? 'readonly' : '' }} style="min-width:100px">{{ $item->receive_notes }}</textarea>
                            </div>
                            <div class="col-md-4">
                              <label>{{ trans('order.cancelled_notes') }}</label>
                              <textarea data-item-id="{{ $item->id }}" id="cancelled_notes_{{ $item->id }}" class="cancelled_notes form-control item-cancelled_notes"
                                        name="cancelled_notes" cols="20" {{ $item->status != 'cancelled' ? 'readonly' : '' }}
                                        style="min-width:100px">{{ $item->cancelled_notes }}</textarea>
                            </div>

                            <div class="col-12">
                              <br>
                            </div>

                            <div class="col-md-12">
                              <div class="position-absolute bottom-0 end-0 d-flex gap-2">
                                @can('delete buy ship items')
                                  <button class="delete-item btn btn-sm btn-danger me-2 mb-2" data-item-id="{{ $item->id }}">
                                    <i class="la la-window-close fs-4 me-0"></i>
                                  </button>
                                @endcan
                                @can('update buy ship items')
                                  <button type="submit" class="save-item btn btn-sm btn-success me-2 mb-2" data-item-id="{{ $item->id }}">
                                    <i class="la la-upload fs-4 me-0"></i>
                                  </button>
                                @endcan
                              </div>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
              <div class="modal bulk-items-modal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form class="bulk-data-form"
                            enctype="multipart/form-data">
                        <div class="row">
                          <div class="col-md-3">
                            <label>{{ trans('order.receive_date') }}</label>
                            <input id="receive_date_bulk" name="receive_date" type="date"
                                   class="form-control" disabled>
                          </div>

                          <div class="col-md-3">
                            <label>{{ trans('order.check_date') }}</label>
                            <input id="check_date_bulk" name="check_date" type="date"
                                   class="form-control">
                          </div>

                          <div class="col-md-3">
                            <label>{{ trans('order.status') }}</label>
                            <select class="status_bulk form-control" name="status">
                              <option value="requested" selected>requested</option>
                              <option value="checked">checked</option>
                              <option value="waiting">waiting</option>
                              <option value="received">received</option>
                              <option value="cancelled">cancelled</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label>{{ trans('order.check_image') }}</label>
                            <input id="check_image_bulk" name="check_image" type="file" size="1000000" accept="image/*" disabled>
                          </div>
                          <div class="col-12">
                            <br>
                          </div>

                          <div class="col-md-4">
                            <label>{{ trans('order.check_notes') }}</label>
                            <textarea id="check_notes_bulk" class="check_notes form-control"
                                      name="check_notes" cols="20" style="min-width:100px"></textarea>
                          </div>
                          <div class="col-md-4">
                            <label>{{ trans('order.receive_notes') }}</label>
                            <textarea  id="receive_notes_bulk" class="receive_notes form-control"
                                       name="receive_notes" cols="20" style="min-width:100px" disabled></textarea>
                          </div>
                          <div class="col-md-4">
                            <label>{{ trans('order.cancelled_notes') }}</label>
                            <textarea id="cancelled_notes_bulk" class="cancelled_notes form-control"
                                      name="cancelled_notes" cols="20"
                                      style="min-width:100px" disabled></textarea>
                          </div>

                        </div>
                        <div class="col-12">
                          <br>
                        </div>
                        <div class="col-12">
                          <div class="position-absolute bottom-0 end-0 d-flex gap-2">
                            @can('update buy ship items')
                              <button type="submit" class="bulk-items btn btn-sm btn-success me-2 mb-2">
                                <i class="la la-upload fs-4 me-0"></i>
                              </button>
                            @endcan
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endcan
  </div>
  @include('buy_ship_orders.components.createSupplierModal')
@endsection
@section('scripts')
  <script>
    let contNum = {{$count}};
    $(document).ready(function () {
      $('.save-item-form').submit(function(e) {
        e.preventDefault();
        let form = $(this), createBtn = form.find('button[type="submit"]');
        createBtn.attr('disabled','disabled');

        // Create a FormData object
        let formData = new FormData(form[0]);

        // Append the CSRF token to the FormData
        formData.append('_token', '{{ csrf_token() }}');

        createBtn.html('<i class="la la-sync fs-4 me-0"></i>');

        $.ajax({
          data: formData,
          url: form.attr('action'),
          type: form.attr('method'),
          contentType: false,
          processData: false,
          dataType: 'json',
          success: function(data) {

            $('#items').load(" #items",function(){
              toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-center",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "3000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "swing",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
              };
              toastr["success"](data.success);
              createBtn.removeAttr('disabled');
              createBtn.html('{{ trans('order.add-item') }}');
              initJquery();
            });

          },
          error: function(xhr) {

            if (xhr.status == 422) {
              const keys = Object.keys(xhr.responseJSON.errors);
              keys.forEach((key, index) => {
                createBtn.removeAttr('disabled');
                toastr["error"](`${xhr.responseJSON.errors[key]}`);
              });
            }

            createBtn.html('{{ trans('order.add-item') }}');
          }
        });
      });
      $('.dynamic-select').on('change', function () {
        var selectedOption = $(this).find(':selected');

        // Retrieve data attributes from the selected option
        var storeNumber = selectedOption.data('store-number');
        var phone = selectedOption.data('phone');

        var itemId = $(this).data('item-id');

        // Update the phone and store_number inputs directly
        $('#phone_' + itemId).val(phone);
        $('#store_number_' + itemId).val(storeNumber);
      });
    });
    $('#submitCreateSupplierForm').on('click', function (e) {
      e.preventDefault(); // Prevent the default form submission
      storeSupplierAndUpdateSelects(); // Call the function to handle form submission
    });
    function createSupplier(){
      $('#createSupplierModal').modal('show');
    }

    function initJquery(){
      initiateSelect2();
      $(".select-effector").each(function () {
        if($(this).is(':checked'))
          $($(this).attr('data-effect')).removeClass('hide').removeClass('hide-on-print');
        else
          $($(this).attr('data-effect')).addClass('hide').addClass('hide-on-print');
      });
      $(".select-effector").change(function(){
        console.log(1);
        if($(this).is(':checked'))
          $($(this).attr('data-effect')).removeClass('hide').removeClass('hide-on-print');
        else
          $($(this).attr('data-effect')).addClass('hide').addClass('hide-on-print');
      });

      $('#check-all-items').change(function() {
        $('.check-item').prop('checked', $(this).prop('checked'));
        if($(this).is(":checked")){
          $(".check-item").closest('table').find("tr").each(function () {
            $(this).removeClass('hide-on-print');
          })
        }else{
          $(".check-item").closest('table').find("tr").each(function () {
            $(this).addClass('hide-on-print');
          })
        }
        collectItems();
        checkLength();
      });

      let checkLength = () => {
        if($('.check-item:checked').length){
          $(".update-item").attr('disabled', false);
          $(".delete-item-bulk").attr('disabled', false);
        }else{
          $(".update-item").attr('disabled', true);
          $(".delete-item-bulk").attr('disabled', true);
        }
      }
      $('.check-item').on('change', function() {
        let allChecked = $('.check-item:checked').length === $('.check-item').length;
        $('#check-all-items').prop('checked', allChecked);
        if($(this).is(":checked")){
          $(this).closest('tr').removeClass('hide-on-print');
        }else{
          $(this).closest('tr').addClass('hide-on-print');
        }
        collectItems();
        checkLength();
      }).trigger('change');

      function collectItems() {
        let quantity_sum = 0, carton_sum = 0, cbm_sum = 0, weight_sum = 0, total_sum = 0;
        $('.check-item:checked').each(function () {
          carton_sum += parseFloat($(this).closest('tr').find('.data-quantity').data("quantity"));
          // quantity_sum += parseFloat($(this).closest('tr').find('.data-quantity').data("sum"));
          cbm_sum += parseFloat($(this).closest('tr').find('.data-cbm').data('cbm'));
          weight_sum += parseFloat($(this).closest('tr').find('.data-weight').data('weight'));
          total_sum += parseFloat($(this).closest('tr').find('.data-total').data('total'));
        })
        $('#d-quantity').html(carton_sum);
        $('#d-cbm').text(cbm_sum);
        $('#d-weight').text(weight_sum);
        $('#d-total').text(total_sum);
      }

      checkLength();

      $(".delete-item-bulk").off('click');
      $(".delete-item-bulk").click(function () {
        let createBtn = $(this);
        Swal.fire({
          title: "{{trans('order.destroy_bulk')}}",
          showCancelButton: true,
          confirmButtonText: "{{trans('delete')}}",
        }).then((result) => {
          if (result.isConfirmed) {
            createBtn.attr('disabled', true);
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');

            if(!$(".check-item:checked").length){
              toastr["error"]("please select at least one item");
              createBtn.removeAttr('disabled');
              return;
            }

            $(".check-item:checked").each(function () {
              formData.append("items[]", $(this).attr('data-id'));
            });

            $.ajax({
              url: '{{route("buy_ship_items.delete_items")}}',
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              dataType: 'json',
              success: function(response) {
                if(response.error){
                  Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: response.message,
                  });
                  createBtn.removeAttr('disabled');
                  return false;
                }
                $('#items').load(" #items",function(){
                  initJquery();
                });
              },
              error: function(xhr) {

                if (xhr.status == 422) {
                  const keys = Object.keys(xhr.responseJSON.errors);
                  keys.forEach((key, index) => {
                    createBtn.removeAttr('disabled');
                    toastr["error"](`${xhr.responseJSON.errors[key]}`);
                  });
                }

              }
            });
          }
        });
      });

      $(".update-item").off('click');
      $(".update-item").click(function () {
        let checked = $(".check-item:checked");
        if(!checked.length){
          toastr["error"]("please select at least one item");
          return;
        }

        if(checked.length === 1){

          $(".single-item-modal-"+checked.data("id")).modal("show")

        }else{

          $(".bulk-items-modal").modal("show")

        }

      });

      $('.bulk-items').off('click').click(function(e) {
        var createBtn = $(this);
        createBtn.attr('disabled','disabled');
        e.preventDefault();
        // Create a FormData object
        var formData = new FormData(createBtn.closest('.bulk-data-form')[0]);

        // Append the CSRF token to the FormData
        formData.append('_token', '{{ csrf_token() }}');

        $(".check-item:checked").each(function () {
          formData.append("items[]", $(this).attr('data-id'));
        });

        $(this).html('<i class="la la-sync fs-4 me-0"></i>');

        $.ajax({
          data: formData,
          url: "{{ route('buy_ship_items.update_bulk') }}",
          type: "POST",
          contentType: false, // Important to prevent jQuery from automatically setting the content type
          processData: false, // Important to prevent jQuery from processing the data
          dataType: 'json',
          success: function(data) {

            console.log('success');
            createBtn.html('<i class="la la-upload fs-4 me-0"></i>');
            $(".modal").modal('hide');

            createBtn.removeAttr('disabled');
            toastr.options = {
              "closeButton": true,
              "debug": false,
              "newestOnTop": false,
              "progressBar": true,
              "positionClass": "toast-top-center",
              "preventDuplicates": false,
              "onclick": null,
              "showDuration": "300",
              "hideDuration": "1000",
              "timeOut": "3000",
              "extendedTimeOut": "1000",
              "showEasing": "swing",
              "hideEasing": "swing",
              "showMethod": "fadeIn",
              "hideMethod": "fadeOut"
            };
            toastr["success"](data.success);
            $('#items').load(" #items",function(){
              initJquery();
            });
          },
          error: function(xhr) {

            if (xhr.status == 422) {
              const keys = Object.keys(xhr.responseJSON.errors);
              keys.forEach((key, index) => {
                createBtn.removeAttr('disabled');
                toastr["error"](`${xhr.responseJSON.errors[key]}`);
              });
            }

            createBtn.html('<i class="la la-upload fs-4 me-0"></i>');
          }
        });
      });

      $('.save-item').off('click').click(function(e) {
        var createBtn = $(this);
        createBtn.attr('disabled','disabled');
        e.preventDefault();
        var form_id = $(this).data('item-id');

        // Create a FormData object
        var formData = new FormData(createBtn.closest('.items-data-form')[0]);

        // Append the CSRF token to the FormData
        formData.append('_token', '{{ csrf_token() }}');

        $(this).html('<i class="la la-sync fs-4 me-0"></i>');

        $.ajax({
          data: formData,
          url: "{{ route('buy_ship_items.store') }}",
          type: "POST",
          contentType: false, // Important to prevent jQuery from automatically setting the content type
          processData: false, // Important to prevent jQuery from processing the data
          dataType: 'json',
          success: function(data) {

            console.log('success');
            createBtn.html('<i class="la la-upload fs-4 me-0"></i>');
            $(".modal").modal('hide');
            $('#items').load(" #items",function(){  initJquery();});

            createBtn.removeAttr('disabled');
            toastr.options = {
              "closeButton": true,
              "debug": false,
              "newestOnTop": false,
              "progressBar": true,
              "positionClass": "toast-top-center",
              "preventDuplicates": false,
              "onclick": null,
              "showDuration": "300",
              "hideDuration": "1000",
              "timeOut": "3000",
              "extendedTimeOut": "1000",
              "showEasing": "swing",
              "hideEasing": "swing",
              "showMethod": "fadeIn",
              "hideMethod": "fadeOut"
            };
            toastr["success"](data.success);
          },
          error: function(xhr) {

            if (xhr.status == 422) {
              const keys = Object.keys(xhr.responseJSON.errors);
              keys.forEach((key, index) => {
                createBtn.removeAttr('disabled');
                toastr["error"](`${xhr.responseJSON.errors[key]}`);
              });
            }

            createBtn.html('<i class="la la-upload fs-4 me-0"></i>');
          }
        });
      });
      $('.delete-item').off('click').click(function(e) {
        e.preventDefault();
        var form_id = $(this).data('randcode') || $(this).data('item-id');
        $(this).html('<i class="la la-sync fs-4 me-0"></i>');
        Swal.fire({
          title: "{{ app()->getLocale() == 'ar' ? 'هل انت متأكد من حذف هذا البند؟' : 'Do you want to delete this item?' }}",
          showCancelButton: true,
          confirmButtonText: "Delete",
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            $.ajax({
              url: "{{ URL::to('buy_ship_items') }}" + '/' + form_id,
              type: "DELETE",
              dataType: 'json',
              success: function(data) {
                console.log('success');
                $('#items').load(' #items', function () {
                  initJquery();
                });
                $('.delete-item').html('<i class="la la-window-close fs-4 me-0"></i>');
                toastr.options = {
                  "closeButton": true,
                  "debug": false,
                  "newestOnTop": false,
                  "progressBar": true,
                  "positionClass": "toast-top-center",
                  "preventDuplicates": false,
                  "onclick": null,
                  "showDuration": "300",
                  "hideDuration": "1000",
                  "timeOut": "3000",
                  "extendedTimeOut": "1000",
                  "showEasing": "swing",
                  "hideEasing": "swing",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
                };

                toastr["warning"](data.success);
              },
              error: function(data) {
                console.log(data);
                $('.save-item').html('<i class="la la-window-close fs-4 me-0"></i>');
                toastr["error"]("Something went wrong");
              }
            });
          } else {
            $('.save-item').html('<i class="la la-window-close fs-4 me-0"></i>');
          }
        });


      });
      $('.status_bulk').off('change').change(function(e) {
        let value = $(this).find('option:selected').attr('value');
        console.log(value);
        if (value === 'checked') {
          $('#receive_notes_bulk').removeAttr('disabled');
        } else {
          $('#receive_notes_bulk').attr('disabled', true);
        }

        if (value === 'requested') {
          $('#check_notes_bulk').removeAttr('disabled')
          $('#check_date_bulk').removeAttr('disabled')
        } else {
          $('#check_notes_bulk').attr('disabled', true)
          $('#check_date_bulk').attr('disabled', true)
        }

        if (value === 'cancelled') {
          $('#cancelled_notes_bulk').removeAttr('disabled')
        } else {
          $('#cancelled_notes_bulk').attr('disabled', true)
        }

        if (value === 'checked') {
          $('#check_image_bulk').removeAttr('disabled')
        } else {
          $('#check_image_bulk').attr('disabled', true)
        }
        if (value === 'waiting') {
          $('#receive_date_bulk').removeAttr('disabled')
        } else {
          $('#receive_date_bulk').attr('disabled', true)
        }
      });

      $('.item-status').off('change').change(function(e) {
        let value = $(this).find('option:selected').attr('value');
        let form = $(this).closest('form');
        if (value === 'checked') {
          form.find('.item-receive_notes').removeAttr('disabled');
          form.find('.item-receive_notes').removeAttr('readonly');
        } else {
          form.find('.item-receive_notes').attr('disabled', true);
        }

        if (value === 'requested') {
          form.find('.item-check_notes').removeAttr('disabled')
          form.find('.item-check_date').removeAttr('disabled')
          form.find('.item-check_notes').removeAttr('readonly')
          form.find('.item-check_date').removeAttr('readonly')
        } else {
          form.find('.item-check_notes').attr('disabled', true)
          form.find('.item-check_date').attr('disabled', true)
        }

        if (value === 'cancelled') {
          form.find('.item-cancelled_notes').removeAttr('disabled')
          form.find('.item-cancelled_notes').removeAttr('readonly')
        } else {
          form.find('.item-cancelled_notes').attr('disabled', true)
        }

        if (value === 'checked') {
          form.find('.item-check_image').removeAttr('disabled')
          form.find('.item-check_image').removeAttr('readonly')
        } else {
          form.find('.item-check_image').attr('disabled', true)
        }
        if (value === 'waiting') {
          form.find('.item-receive_date').removeAttr('disabled')
          form.find('.item-receive_date').removeAttr('readonly')
        } else {
          form.find('.item-receive_date').attr('disabled', true)
        }
      });
    }

    initJquery();

    // Function to update Select2 options
    function updateSelect2Options(data) {
      // Assuming 'mySelect2' is the ID of your Select2 element
      var select2 = $('select.select2');

      // Clear existing options
      select2.empty();

      // Add new options based on the response data
      $.each(data.suppliers, function(index, supplier) {
        var option = new Option(supplier.name + ' - ' + supplier.code, supplier.id);
        // Add additional attributes to the option
        $(option).attr({
          'data-store-number': supplier.store_number,
          'data-phone': supplier.phone
          // Add more attributes as needed
        });

        select2.append(option);
      });

      // Trigger change event to refresh Select2
      select2.trigger('change');
    }
    function storeSupplierAndUpdateSelects(){
      $.ajax({
        type: 'POST',
        url: $('#createSupplierForm').attr('action'),
        data: $('#createSupplierForm').serialize(),
        success: function(response) {
          if (response.success) {
            toastr.options = {
              "closeButton": true,
              "debug": false,
              "newestOnTop": false,
              "progressBar": true,
              "positionClass": "toast-top-center",
              "preventDuplicates": false,
              "onclick": null,
              "showDuration": "300",
              "hideDuration": "1000",
              "timeOut": "3000",
              "extendedTimeOut": "1000",
              "showEasing": "swing",
              "hideEasing": "swing",
              "showMethod": "fadeIn",
              "hideMethod": "fadeOut"
            };
            toastr["success"](response.message);
            console.log(response);
            updateSelect2Options(response);
            $('#createSupplierModal').modal('hide');
          } else {
            alert('Failed to create supplier.'); // Display error message
          }
        },
        error: function(xhr, status, error) {
          console.error('AJAX request failed:', status, error);
          alert('Failed to create supplier.'); // Display error message
        }
      });
    }
    function  initiateSelect2(){
      $('.supplier_select').select2(
        {
          allowClear: true,
          placeholder: 'This is my placeholder',
          language: {
            noResults: function() {
              return `<button style="width: 100%;font-size:12px" type="button"
             class="btn btn-primary"
             onClick='createSupplier()'>Add Supplier</button>
             </li>`;
            }
          },

          escapeMarkup: function (markup) {
            return markup;
          }
        }
      );
      $('.supplier_select2').each(function () {
        $(this).select2(
          {
            allowClear: true,
            dropdownParent: $(this).closest('.modal'),
            placeholder: 'This is my placeholder',
            language: {
              noResults: function() {
                return `<button style="width: 100%;font-size:12px" type="button"
             class="btn btn-primary"
             onClick='createSupplier()'>Add Supplier</button>
             </li>`;
              }
            },

            escapeMarkup: function (markup) {
              return markup;
            }
          }
        );
      });
      $('#client').select2();
      $('#repo').select2();
    }
    $(document).ready(function() {
      updateAllTotal();
      initiateSelect2();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      function updateAllTotal() {
        var sum = 0;
        // Loop through all inputs with name "total"
        $('input[name="total"]').each(function() {
          if($(this).attr("data-status") === "cancelled") return;
          var value = parseFloat($(this).val()) || 0;
          sum += value;
        });
        // Update the value of the input with id "allTotal"
        $('#allTotal').val(sum.toFixed(2));
      }

      function generateRandomString(length) {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let randomString = '';

        for (let i = 0; i < length; i++) {
          const randomIndex = Math.floor(Math.random() * characters.length);
          randomString += characters.charAt(randomIndex);
        }
        const timeStamp = new Date().getTime();
        randomString += timeStamp.toString();
        return randomString;
      }

      $('#add-item').on('click', function() {

        var randomCode = generateRandomString(10);
        var newCard = `
            <div class="card mb-50">
                <input type="checkbox" class="form-check-input ms-3 mt-3 select-card">
                <span class="int_num">#${contNum++}</span>
                <div class="row p-5">
                    <form id="create-item-form-${randomCode}" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-3">
                                <input name="order_id" type="hidden" value="{{ $order->id }}">
                                <label>{{ trans('order.carton_code') }}</label>
                                <input name="carton_code" type="text" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ trans('order.item') }}</label>
                                <input name="item" type="text" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ trans('order.carton_quantity') }}</label>
                                <input name="carton_quantity" type="number" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ trans('order.pieces_number') }}</label>
                                <input name="pieces_number" type="number" class="form-control">
                            </div>

                            <div class="col-12">
                              <br>
                            </div>

                            <div class="col-md-3">
                                <label>{{ trans('order.single_price') }}</label>
                                <input name="single_price" type="number" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ trans('order.total') }}</label>
                                <input name="total" type="number" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ trans('order.check_date') }}</label>
                                <input name="check_date" type="date" class="form-control">
                            </div>
                            <div class="col-md-3">
                              <label>{{ trans('order.receive_date') }}</label>
                              <input  name="receive_date" type="date" class="form-control"
                                disabled>
                            </div>

                            <div class="col-12">
                              <br>
                            </div>

                            <div class="col-md-3">
                                <label>{{ trans('order.cbm') }}</label>
                                <input name="cbm" type="number" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ trans('order.weight') }}</label>
                                <input name="weight" type="number" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ trans('order.status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="requested" selected>requested</option>
                                    <option value="checked" disabled>checked</option>
                                    <option value="waiting" disabled>waiting</option>
                                    <option value="received" disabled>received</option>
                                    <option value="shipped" disabled>shipped</option>
                                    <option value="cancelled" disabled>cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>{{ trans('order.supplier') }}</label>
                                <select class="select2 supplier_select" name="supplier_id" class="form-control">
                                  <option>select supplier</option>
                              @foreach ($suppliers as $supplier)
                                <option data-store-number={{ $supplier->store_number }}" data-phone={{ $supplier->phone }}" value="{{$supplier->id}}">{{$supplier->name}} - {{$supplier->code}}</option>
                                                        @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                              <br>
                            </div>

                            <div class="col-md-4">
                              <label>{{ trans('client.mark') }}</label>
                              <input type="text" name="mark" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>{{ trans('order.check_image') }}</label>
                                <input name="check_image" type="file" size="1000000" accept="image/*">
                            </div>
                            <div class="col-md-4">
                                <label>{{ trans('order.container_number') }}</label>
                                <input name="container_number" type="text" class="form-control" readonly>
                            </div>

                            <div class="col-12">
                              <br>
                            </div>

                            <div class="col-md-4">
                                <label>{{ trans('order.check_notes') }}</label>
                                <textarea name="check_notes"  cols="20" class="form-control" style="min-width:100px"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label>{{ trans('order.receive_notes') }}</label>
                                <textarea name="receive_notes"  cols="20" class="form-control" style="min-width:100px" readonly></textarea>
                            </div>
                            <div class="col-md-4">
                                <label>{{ trans('order.cancelled_notes') }}</label>
                                <textarea name="cancelled_notes"  cols="20" class="form-control" style="min-width:100px" readonly></textarea>
                            </div>
                        </div>
                        <div class="position-absolute bottom-0 end-0 d-flex gap-2">
                            <button class="delete-item btn btn-sm btn-danger me-2 mb-2" data-randCode="${randomCode}">
                                <i class="la la-window-close fs-4 me-0"></i>
                            </button>
                            <button class="save-item btn btn-sm btn-success me-2 mb-2" data-randCode="${randomCode}">
                                <i class="la la-upload fs-4 me-0"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            `;
        $('#items').append(newCard);
        initiateSelect2()
      });
      $(document).on('input',
        'input[name="single_price"], input[name="carton_quantity"], input[name="pieces_number"], input[name="cbm"], input[name="weight"]',
        function() {
          updateTotal($(this));
          updateAllTotal();
        });
      // Function to update the total based on the input fields
      function updateTotal(currentInput) {
        var container = currentInput.closest('form');
        var singlePrice = parseFloat(container.find('input[name="single_price"]').val()) || 0;
        // var cartonQuantity = parseFloat(container.find('input[name="carton_quantity"]').val()) || 0;
        var cartonQuantity = parseFloat(container.find('input[name="carton_quantity"]').val()) || 0;
        var piecesNumber = parseFloat(container.find('input[name="pieces_number"]').val()) || 0;

        // Calculate the total and update the total input field
        var total = singlePrice * cartonQuantity * piecesNumber;
        container.find('input[name="total"]').val(total.toFixed(2)); // You can adjust the precision as needed
      }

      $('#print-items').click(function() {

        let printWindow = window.open('', '_blank');
        printWindow.document.open();
        let content = '<html><head><title>Print</title>';

        $("head").find("link").each(function () {
          content += '<link href="' + $(this).attr('href') + '">';
        });
        content += '<style>.no-print{display: none !important;}.hide-on-print{display: none !important;}</style></head><body>' +
          $(".print-content").html() + '</body></html>';

        printWindow.document.write(content);

        printWindow.document.close();

        // Wait for content to load before printing
        printWindow.addEventListener('DOMContentLoaded', () => {
          printWindow.print();
          printWindow.close();
        })
      });

      $("#print").on('click', function () {
        // Determine the direction based on the locale
        var direction = '{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}';

        // Create a temporary container
        var tempContainerId = 'tempPrintContainer';
        $('<div id="' + tempContainerId + '" style="direction: ' + direction + '"></div>').appendTo('body');

        var selectedItemsHTML = '';
        var headerLabels = [];
        var firstIteration = true;

        $('.card.selected').each(function () {
          var rowData = [];
          $(this).find('input.selected, select.selected, textarea.selected').each(function () {
            var label = $(this).prev('label').text();
            var value = $(this).val();

            if (firstIteration) {
              headerLabels.push(label);
            }

            rowData.push({ label: label, value: value });
          });

          selectedItemsHTML += '<tr>';
          rowData.forEach(function (data) {
            selectedItemsHTML += '<td>' + data.value + '</td>';
          });
          selectedItemsHTML += '</tr>';

          firstIteration = false;
        });

        // Create the table
        var tableHTML = '<table class="table table-bordered" style="direction: ' + direction + '"><thead><tr>';
        headerLabels.forEach(function (label) {
          tableHTML += '<th>' + label + '</th>';
        });
        tableHTML += '</tr></thead><tbody>' + selectedItemsHTML + '</tbody></table>';

        // Append the HTML content to the temporary container
        $('#' + tempContainerId).html('<div id="printable-table">' + tableHTML + '</div>');

        // Print the selected items
        printJS({
          printable: tempContainerId,
          type: 'html',
          style: `
                .table-bordered {
                    border-collapse: collapse;
                    width: 100%;
                }

                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }

                th {
                    background-color: black;
                    color:white;
                }
            `,
          gridStyle: 'table-bordered',
        });

        // Remove the temporary container from the DOM
        $('#' + tempContainerId).remove();
      });



      // Checkbox functionality
      $('#select-all').on('change', function() {
        // Check or uncheck all checkboxes with class 'select-card'
        $('.select-card').prop('checked', $(this).prop('checked'));
        // Add or remove class 'selected' based on the status of the checkbox
        updateCardSelection();
      });

      // Individual checkbox functionality
      $('body').on('change', '.select-card', function() {
        // Uncheck "select-all" checkbox if any individual checkbox is unchecked
        if (!$(this).prop('checked')) {
          $('#select-all').prop('checked', false);
        }
        // Add or remove class 'selected' based on the status of the checkbox
        updateCardSelection();
      });
      // Function to update the card selection based on checkbox status
      function updateCardSelection() {
        $('.select-card').each(function() {
          var card = $(this).closest('.card');
          if ($(this).prop('checked')) {
            card.addClass('selected');
          } else {
            card.removeClass('selected');
          }
        });
      }
      function handleCheckboxChange() {
        // Clear previously selected items
        $('#items input, #items textarea, #items select').removeClass('selected');

        // Loop through checked checkboxes in check-columns
        $('#check-columns input:checked').each(function() {
          // Get the id of the checkbox
          var checkboxId = $(this).attr('id');

          // Add selected class to items with corresponding name attribute for input and textarea
          $('#items input[name="' + checkboxId + '"]').addClass('selected');
          $('#items textarea[name="' + checkboxId + '"]').addClass('selected');

          // Add selected class to items with corresponding name attribute for select elements
          $('#items select[name="' + checkboxId + '"]').addClass('selected');
        });
      }


      // Attach the function to checkbox change event
      $('#check-columns input').change(handleCheckboxChange);
    });
  </script>

@endsection
