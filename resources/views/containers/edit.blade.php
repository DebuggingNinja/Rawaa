@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
<div class="container-fluid" id="items-data">
  <div class="row">
    <div class="col-lg-12">
      <div class="d-flex align-items-center user-member__title mb-30 mt-30">
        <h4 class="text-capitalize">{{ trans('container.edit-container') }}</h4>
      </div>
    </div>
  </div>
  <div class="card mb-50">
    <div class="row p-5">
      <div class="col-12">
        <form action="{{ route('containers.update', $container->id) }}" method="POST" enctype="multipart/form-data">
          @method('put')
          @csrf
          <div class="row">
            <div class="col-12 col-md-3">
              <div class="form-group mb-25">
                <label for="broker" class="color-dark fs-14 fw-500 align-center">
                  {{ trans('broker.brokers') }}
                  <span class="text-danger">*</span>
                </label>
                <select class="form-select" name="broker" id="broker">
                  <option value="">---</option>
                  @foreach ($brokers as $broker)
                  <option value="{{ $broker->id }}" {{ $container->broker_id == $broker->id ? 'selected' : '' }}>
                    {{ $broker->name }} - {{$broker->code}}</option>
                  @endforeach
                </select>
                @if ($errors->has('broker'))
                <p class="text-danger">{{ $errors->first('broker') }}</p>
                @endif
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-group mb-25">
                <label for="repo" class="color-dark fs-14 fw-500 align-center">
                  {{ trans('repo.repos') }}
                  <span class="text-danger">*</span>
                </label>
                <select class="form-select " name="repo" id="repo">
                  <option value="">---</option>
                  @foreach ($repos as $repo)
                  <option value="{{ $repo->id }}" {{ $container->repository_id == $repo->id ? 'selected' : '' }}>
                    {{$repo->name . ' - ' . $repo->code}}</option>
                  @endforeach
                </select>
                @if ($errors->has('repo'))
                <p class="text-danger">{{ $errors->first('repo') }}</p>
                @endif
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-group mb-25">
                <label for="company" class="color-dark fs-14 fw-500 align-center">
                  {{ trans('company.companies') }}
                  <span class="text-danger">*</span>
                </label>
                <select class="form-select " name="company" id="company">
                  <option value="">---</option>
                  @foreach ($companies as $company)
                  <option value="{{ $company->id }}" {{ $container->shipping_company_id == $company->id ? 'selected' :
                    '' }}>{{ $company->name }}
                  </option>
                  @endforeach
                </select>
                @if ($errors->has('company'))
                <p class="text-danger">{{ $errors->first('company') }}</p>
                @endif
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-group mb-25">
                <label for="company" class="color-dark fs-14 fw-500 align-center">
                  {{ trans('container.shipping_type') }}
                  <span class="text-danger">*</span>
                </label>
                <select class="form-select " name="shipping_type" id="shipping_type">
                  <option value="">---</option>
                  <option value="complete" {{ $container->shipping_type == 'complete' ? 'selected' : '' }}>complete
                  </option>
                  <option value="partial" {{ $container->shipping_type == 'partial' ? 'selected' : '' }}>partial
                  </option>
                </select>
                @if ($errors->has('shipping_type'))
                <p class="text-danger">{{ $errors->first('shipping_type') }}</p>
                @endif
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-group mb-25">
                <label for="number" class="color-dark fs-14 fw-500 align-center">{{ trans('container.number') }}
                  <span class="text-danger">*</span></label>
                <input type="text" class="form-control ih-medium ip-gray radius-xs b-light px-15" name="number"
                  id="number" value="{{ $container->number }}">
                @if ($errors->has('number'))
                <p class="text-danger">{{ $errors->first('number') }}</p>
                @endif
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-group mb-25">
                <label for="lock_number" class="color-dark fs-14 fw-500 align-center">{{ trans('container.lock_number')
                  }}
                  <span class="text-danger">*</span></label>
                <input type="text" class="form-control ih-medium ip-gray radius-xs b-light px-15" name="lock_number"
                  id="lock_number" value="{{ $container->lock_number }}">
                @if ($errors->has('lock_number'))
                <p class="text-danger">{{ $errors->first('lock_number') }}</p>
                @endif
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-group mb-25">
                <label for="destination" class="color-dark fs-14 fw-500 align-center">{{ trans('container.destination')
                  }}
                  <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control ih-medium ip-gray radius-xs b-light px-15" name="destination"
                  id="destination" value="{{ $container->destination }}">
                @if ($errors->has('destination'))
                <p class="text-danger">{{ $errors->first('destination') }}</p>
                @endif
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-group mb-0 form-group-calender">
                <label for="est_arrive_date" class="color-dark fs-14 fw-500 align-center">{{
                  trans('container.est_arrive_date') }}
                  <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control form-control-lg" id="est_arrive_date" name="est_arrive_date"
                  value="{{ $container->est_arrive_date }}">
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-group mb-0 form-group-calender">
                <label for="cost_rmb" class="color-dark fs-14 fw-500 align-center">{{
                  trans('container.cost_rmb') }}
                </label>
                <input type="text" class="form-control form-control-lg" id="cost_rmb" name="cost_rmb"
                  value="{{ $container->cost_rmb }}">
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="form-group mb-0 form-group-calender">
                <label for="cost_dollar" class="color-dark fs-14 fw-500 align-center">{{
                  trans('container.cost_dollar') }}
                </label>
                <input type="text" class="form-control form-control-lg" id="cost_dollar" name="cost_dollar"
                  value="{{ $container->cost_dollar }}">
              </div>
            </div>
            <div class="button-group d-flex pt-25 justify-content-md-end justify-content-stretch">
              <button type="submit"
                class="btn btn-primary btn-default btn-squared radius-md shadow2 btn-sm">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div id="ajaxPart" class="row">
    <div class="col-lg-12 mb-30">
      <div class="card">
        <div class="card-header color-dark fw-500">
          <div>
            {{ trans('container.items') }}
          </div>
          <div class="d-flex gap-1">
            <button class="btn btn-primary" id="print">{{ trans('print') }}</button>
            <button class="btn btn-primary save-items" id="print">{{ trans('save') }}</button>
          </div>
        </div>
        <div class="card-body">
          <div class="userDatatable global-shadow border-light-0 w-100" dir="auto">
            <div class="table-responsive">
              <table class="table mb-0 table-borderless" id="items-table" cellpadding="1">
                <thead>
                  <tr class="userDatatable-header">
                    <th>
                      <span class="userDatatable-title">{{ trans('id') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('client.name') }}</span>
                    </th>

                    <th>
                      <span class="userDatatable-title">{{ trans('client.code') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('client.address') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('order.carton_quantity') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('order.cbm') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('order.weight') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('order.collect_by') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('order.price') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('total') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('notes') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('actions') }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  @if (!count($items))
                  <tr>
                    <td colspan="7">
                      <p class="text-center">No Items Found !</p>
                    </td>
                  </tr>
                  @else
                  @php
                  $counter = 1;
                  @endphp
                  @foreach ($items as $item)
                  <tr>
                    <form class="item-form" id="item-{{ $item->item->id }}" data-item-id="{{ $item->item->id }}" method="POST">
                      @csrf
                      <input type="hidden" class="container_id" name="container_id" value="{{ $container->id }}">
                      <input type="hidden" class="item_id" name="item_id" value="{{ $item->item->id }}">
                      <td>
                        <div class="userDatatable-content">
                          {{ $counter++ }}
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          {{ $item->client->name }}
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          {{ $item->client->code }}
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          {{ $item->client->address }}
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content" data-item='carton'
                          data-item-carton-qty="{{ $item->quantity }}">
                          {{ $item->quantity }}
                        </div>
                        <input type="hidden" name="quantity" value="{{ $item->quantity }}">
                      </td>
                      <td>
                        <div class="userDatatable-content cbm" data-item-id="{{ $item->item->id }}"
                          data-item-cbm="{{ $item->cbm }}">
                          {{ $item->cbm }}
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content weight" data-item-id="{{ $item->item->id }}"
                          data-item-weight="{{ $item->weight }}">
                          {{ $item->weight }}
                        </div>
                      </td>
                      <td>
                        <div class="">
                          <label for="collect_by_cbm-{{ $item->item->id }}">{{ trans('order.cbm') }}</label>
                          <input type="radio" id="collect_by_cbm-{{ $item->item->id }}" class="collect_by" name="collect_by-{{ $item->item->id }}" data-item-id="{{ $item->item->id }}"
                                 value="cbm" @if($item->item->collect_by == "cbm") checked @endif>
                        </div>
                        <div class="">
                          <label for="collect_by_weight-{{ $item->item->id }}">{{ trans('order.weight') }}</label>
                          <input type="radio" id="collect_by_weight-{{ $item->item->id }}" class="collect_by" name="collect_by-{{ $item->item->id }}" data-item-id="{{ $item->item->id }}"
                                 value="weight" @if($item->item->collect_by == "weight") checked @endif>
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          <input class="meter_price" type="text" name="meter_price" data-item-id="{{ $item->item->id }}"
                            value="{{ $item->item->meter_price ?? '' }}">
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          <input class="total" type="text" name="total" readonly data-item-id="{{ $item->item->id }}"
                            value="{{ $item->item->total ?? '' }}">
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          <textarea class="notes" name="notes" rows="1" data-item="notes"
                            data-item-notes="{{ $item->item->notes ?? '' }}">{{ $item->item->notes ?? '' }}</textarea>
                        </div>
                      </td>
                      <td>
                        <button class="btn btn-success save-item" type="submit" data-item-id="{{ $item->item->id }}">
                          <i class="la la-upload fs-4 me-0"></i>
                        </button>
                      </td>
                    </form>
                  </tr>
                  @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(document).ready(function() {
      $('#repo').select2();
      $('#broker').select2();
      $('#company').select2();
      $('#shipping_type').select2();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $("#print").on('click', function() {
        printJS({
          printable: 'items-data',
          type: 'html',
          style: `
            table {border-collapse: collapse;width: 98%;font-size:10px;}
            th, td {border: 1px solid #ddd;text-align: center;width:8%}
            tr{display:table-row;}
            button, .text-danger {display:none!important}
            textarea, input, select {border:none!important;width:50px!important}
            select {appearance:none}
            textarea {resize: none;min-height:100px}
            th:last-of-type, td:last-of-type{display:none}

          `
        });
      });
      $('body').on('click', '.save-item', function(e) {
        e.preventDefault();
        var form_id = $(this).data('item-id');
        $(this).html('<i class="la la-sync fs-4 me-0"></i>');
        $.ajax({
          data: $(`#item-${form_id}`).serialize(),
          url: "{{ route('containers.items') }}",
          type: "POST",
          dataType: 'json',
          success: function(data) {
            console.log('success');
            $('.save-item').html('<i class="la la-upload fs-4 me-0"></i>');
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
            location.reload();
          },
          error: function(xhr) {
            if(xhr.status = 422){
              const keys = Object.keys(xhr.responseJSON.errors);
              keys.forEach((key,index) => {
                toastr["error"](`${xhr.responseJSON.errors[key]}`);
              });
            }
            $('.save-item').html('<i class="la la-upload fs-4 me-0"></i>');
          }
        });
      });
      $('.save-items').click(function(e) {
        e.preventDefault();
        var form_id = $(this).data('item-id');
        $(this).html('<i class="la la-sync fs-4 me-0"></i>');
        let data = new FormData;
        $(".item-form").each(function () {
          let item = {};
          $(this).serializeArray().forEach(function (it){
            item[it.name] = it.value;
          });
          data.append("items[]", JSON.stringify(item));
        });
        $.ajax({
          data: data,
          url: "{{ route('containers.items_all') }}",
          type: "POST",
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(data) {
            console.log('success');
            $('.save-item').html('<i class="la la-upload fs-4 me-0"></i>');
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
            location.reload();
          },
          error: function(xhr) {
            if(xhr.status = 422){
              const keys = Object.keys(xhr.responseJSON.errors);
              keys.forEach((key,index) => {
                toastr["error"](`${xhr.responseJSON.errors[key]}`);
              });
            }
            $('.save-item').html('<i class="la la-upload fs-4 me-0"></i>');
          }
        });
      });
      $('.meter_price').on('input', function(e) {
        var itemId = $(this).data('item-id');
        var meterPrice = $(this).val();
        collectTotal($(this), itemId, meterPrice);
      });
      $('.collect_by').click(function(e) {
        var itemId = $(this).data('item-id');
        var meterPrice = $(".meter_price[data-item-id='" + itemId + "']").val();
        collectTotal($(this), itemId, meterPrice);
      });
      function collectTotal(elem, itemId, meterPrice){
        let collectByCbm = $(".collect_by[data-item-id='" + itemId + "']:checked").val() !== "weight";
        var cbmValue;
        if(collectByCbm){
          cbmValue = parseFloat($('.cbm[data-item-id="' + itemId + '"]').data('item-cbm')) || 0;
        }else{
          cbmValue = parseFloat($('.weight[data-item-id="' + itemId + '"]').data('item-weight')) || 0;
        }
        var totalValue = parseFloat(meterPrice) * cbmValue || 0;
        $('.total[data-item-id="' + itemId + '"]').val(totalValue.toFixed(1));
      }
    });
</script>
@endsection
