@section('title', $title)
@section('description', $description)
@section('style')
<style>
  tr.selected {
    background-color: var(--bs-green);
  }
</style>
@endsection
@extends('layout.app')
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-12">
      <div class="contact-breadcrumb">
        <div class="breadcrumb-main add-contact justify-content-sm-between ">
          <div class=" d-flex flex-wrap justify-content-center breadcrumb-main__wrapper">
            <div class="d-flex align-items-center add-contact__title justify-content-center me-sm-25">
              <h4 class="text-capitalize fw-500 breadcrumb-title">{{$repo->name . ' - ' . $repo->code}}
              </h4>
              <span class="sub-title ms-sm-25 ps-sm-25"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <div class="card p-5">
        <form id="form_select_warehouse" action="{{route('repository.current_items')}}" method="POST">
          @csrf
          @method('POST')
          <div id="select-repo" class="form-group">
            <label for="repo_id">
              {{trans('repo.name')}}
            </label>
            <select class="form-select" name="repo_id" id="repo_id">
              <option value="">Select a Warehouse</option>
              @if (count($repos) == 0)
              <option value="">NO</option>
              @endif
              @foreach ( $repos as $repo)
              <option value="{{$repo->id}}" @if(request()?->repo_id == $repo->id) selected @endif>{{$repo->name.' - '.$repo->code}}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="search">
              {{trans('repo.search')}}
            </label>
            <input class="form-control" name="search" id="search" value="{{request()?->search}}">
          </div>
          <div class="form-group">
            <label for="showShipped">
              {{trans('repo.show_shipped')}}
            </label>
            <input class="" type="checkbox" name="showShipped" id="showShipped"
              @if(request()?->showShipped) checked @endif>
          </div>
          <div class="row search-dates">
            <div class="col-md-6">
              <div class="form-group">
                <label for="start_date">
                  {{trans('repo.start_date')}}
                </label>
                <input class="form-control" type="date" name="start_date" id="start_date"
                       value="{{request()?->start_date ?? date('Y-m-d')}}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="end_date">
                  {{trans('repo.end_date')}}
                </label>
                <input class="form-control" type="date" name="end_date" id="end_date"
                       value="{{request()?->end_date ?? date('Y-m-d', strtotime("+ 1 day"))}}">
              </div>
            </div>
          </div>
          <button class="btn btn-primary" type="submit">{{trans('repo.search')}}</button>
        </form>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-12 mb-30">
      <div class="card">
        <div class="card-header color-dark fw-500">
          <div>
            {{trans('order.items')}}
          </div>
          <div style="display: flex; gap: 10px">
            <button class="btn btn-primary" id="print">{{trans('print')}}</button>
            @if(!request()?->showShipped)
            <button class="btn btn-primary ship-orders" data-bs-target="#load-containers"
                    data-bs-toggle="modal">{{trans('ship')}}</button>
            @endif
          </div>
        </div>
        <div class="card-body">
          <div id="items-data" class="userDatatable global-shadow border-light-0 w-100">
            <div class="table-responsive">
              <table class="table mb-0 table-borderless" id="items-table">
                <thead>
                  <tr class="userDatatable-header">
                    @if(!request()?->showShipped)
                    <th>
                      <div class="custom-checkbox">
                        <input type="checkbox" id="check-all-items">
                        <label for="check-all-items">
                        </label>
                      </div>
                    </th>
                    @endif
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
                      <span class="userDatatable-title">{{ trans('order.carton_code') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('order.item') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ trans('client.mark') }}</span>
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
                      <span class="userDatatable-title">{{ trans('order.receive_date') }}</span>
                    </th>
                    @if(request()?->showShipped)
                      <th>
                        <span class="userDatatable-title">{{ trans('repo.shipping_date') }}</span>
                      </th>
                      <th>
                        <span class="userDatatable-title">{{ trans('container.number') }}</span>
                      </th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @if (count($items) == 0)
                  <tr>
                    <td colspan="7">
                      <p class="text-center">No Items Found !</p>
                    </td>
                  </tr>
                  @else
                  @foreach ($items as $item)
                  <tr>
                    @if(!request()?->showShipped)
                    <td>
                      <div class="custom-checkbox">
                        <input class="check-item" type="checkbox" id="check-{{$item->id}}" data-id="{{$item->id}}">
                        <label for="check-{{$item->id}}">
                        </label>
                      </div>
                    </td>
                    @endif
                    <td>
                      <div class="userDatatable-content">
                        {{ $item->order->client->name }}
                      </div>
                    </td>
                    <td>
                      <div class="userDatatable-content">
                        {{ $item->order->client->code }}
                      </div>
                    </td>
                    <td>
                      <div class="userDatatable-content">
                        {{ $item->order->client->address }}
                      </div>
                    </td>
                    <td>
                      <div class="userDatatable-content">
                        {{ $item->carton_code }}
                      </div>
                    </td>
                    <td>
                      <div class="userDatatable-content">
                        {{ $item->item }}
                      </div>
                    </td>
                    <td>
                      <div class="userDatatable-content">
                        {{ $item->order->client->mark }}
                      </div>
                    </td>
                    <td>
                      <div class="userDatatable-content" data-item='carton'
                        data-item-carton-qty="{{$item->carton_quantity}}">
                        {{ $item->carton_quantity }}
                      </div>
                    </td>
                    <td>
                      <div class="userDatatable-content" data-item='cbm' data-item-cbm="{{$item->cbm}}">
                        {{ $item->cbm }}
                      </div>
                    </td>
                    <td>
                      <div class="userDatatable-content" data-item='weight' data-item-weight="{{$item->weight}}">
                        {{ $item->weight }}
                      </div>
                    </td>
                    <td>
                      <div class="userDatatable-content">
                        {{ $item->receive_date }}
                      </div>
                    </td>
                    @if(request()?->showShipped)
                      <td>
                        <div class="userDatatable-content">
                          {{ $item->shipping_date?->format('Y-m-d') }}
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          {{ $item->container_number }}
                        </div>
                      </td>
                    @endif
                  </tr>
                  @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <td></td>
                    <td colspan="6">{{ trans('order.total') }}</td>
                    <td id="carton_quantity_total">0</td>
                    <td id="cbm_total">0</td>
                    <td id="weight_total">0</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div class="pagination-container d-flex justify-content-end pt-25">
            {{ $items->links( 'pagination::bootstrap-5' ) }}

            <ul class="dm-pagination d-flex">
              <li class="dm-pagination__item">
                <div class="paging-option">
                  <select name="page-number" class="page-selection" onchange="updatePagination( event )">
                    <option value="20" {{ 20==$items->perPage() ? 'selected' : '' }}>20/page</option>
                    <option value="40" {{ 40==$items->perPage() ? 'selected' : '' }}>40/page</option>
                    <option value="60" {{ 60==$items->perPage() ? 'selected' : '' }}>60/page</option>
                  </select>
                  <a href="#" class="d-none per-page-pagination"></a>
                </div>
              </li>
            </ul>

            <script>
              function updatePagination( event ) {
                                    var per_page = event.target.value;
                                    const per_page_link = document.querySelector( '.per-page-pagination' );
                                    per_page_link.setAttribute( 'href', '/pagination-per-page/' + per_page  );

                                    per_page_link.click();
                                }
            </script>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="load-containers"> <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <select class="form-select" name="container" id="container">
          <option value="">Select a Container</option>
          @foreach ( $containers as $container)
            <option value="{{$container->id}}">{{$container->number.'(' . $container->serial_number . ') - '.$container->shipping_date}}</option>
          @endforeach
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary move-to-shipping">Ship items</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {
    $('#repo_id').select2();
    $('#container').select2();
    let checkLength = () => {
      if($('.check-item:checked').length){
        $(".ship-orders").attr('disabled', false);
      }else{
        $(".ship-orders").attr('disabled', true);
      }
    }

    $(".move-to-shipping").click(function () {

      let createBtn = $(this);

      createBtn.attr('disabled', 'disabled');
      let container = $('#container').val();

      if(container === ""){
        toastr["error"]("please select a valid container");
        createBtn.removeAttr('disabled');
        return;
      }

      let formData = new FormData();
      formData.append('_token', '{{ csrf_token() }}');
      formData.append("container", container);

      if(!$(".check-item:checked").length){
        toastr["error"]("please select at least one item");
        createBtn.removeAttr('disabled');
        return;
      }

      $(".check-item:checked").each(function () {
        formData.append("items[]", $(this).attr('data-id'));
      });

      $.ajax({
        url: '{{route("repository.ship")}}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
          if(response.error){
            $("#load-containers").modal('hide');
            Swal.fire({
              icon: "error",
              title: "Error",
              text: response.message,
            });
            createBtn.removeAttr('disabled');
            return false;
          }
          $(".check-item:checked").each(function () {
            $(this).closest('tr').remove();
          });

          $("#load-containers").modal('hide');
          createBtn.removeAttr('disabled');
          Swal.fire({
            icon: "success",
            title: response.message,
          });
          checkLength();
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
    });
    $('.check-item').on('change', () => {
      checkLength();
    });
    checkLength();
      $('#repo_id').on('change', function() {
        // Trigger form submission when the value changes
        $('#form_select_warehouse').submit();
      });
      $("#print").on('click',function(){
        printJS({
          printable: 'items-data',
          type:'html',
          style:`
            table {border-collapse: collapse;width: 100%;}
            th, td {border: 1px solid #ddd;padding: 8px;text-align: center;}
            td:first-child,th:first-child{display:none !important;}
            .custom-checkbox {display: none !important;}
            tr{display:table-row;}
            tbody tr:not(.selected){display:none!important;}
          `});
      });

      $("#showShipped").change(function () {
        $(".search-dates").toggle($(this).is(":checked"));
      }).trigger('change');

            $('#check-all-items').on('change', function() {
                $('.check-item').prop('checked', $(this).prop('checked'));
                checkLength();
                if($(this).prop('checked')){
                  $('.check-item').parent().parent().parent().addClass('selected')
                  var carton_sum = 0;
                  var cbm_sum = 0;
                  var weight_sum = 0;
                  $('#items-table tbody tr').each(function(){
                    var carton_cell_value = $(this).find('td div[data-item="carton"]').data('item-carton-qty');
                    var cbm_cell_value = $(this).find('td div[data-item="cbm"]').data('item-cbm');
                    var weight_cell_value = $(this).find('td div[data-item="weight"]').data('item-weight');
                    carton_sum += parseFloat(carton_cell_value);
                    cbm_sum += parseFloat(cbm_cell_value);
                    weight_sum += parseFloat(weight_cell_value);
                  });
                  $('#carton_quantity_total').text(carton_sum);
                  $('#cbm_total').text(cbm_sum);
                  $('#weight_total').text(weight_sum);
                }else{
                  $('.check-item').parent().parent().parent().removeClass('selected')
                  $('#carton_quantity_total').text(0);
                  $('#cbm_total').text(0);
                  $('#weight_total').text(0);
                }
            });

            // Select only the clicked row when a row checkbox is clicked
            $('.check-item').on('change', function() {
              var allChecked = $('.check-item:checked').length === $('.check-item').length;
              $('#check-all-items').prop('checked', allChecked);
              if($(this).prop('checked')){
                  $(this).parent().parent().parent().addClass('selected');

                    var carton_cell_value = $(this).parent().parent().parent().find('td div[data-item="carton"]').data('item-carton-qty');
                    var cbm_cell_value = $(this).parent().parent().parent().find('td div[data-item="cbm"]').data('item-cbm');
                    var weight_cell_value = $(this).parent().parent().parent().find('td div[data-item="weight"]').data('item-weight');
                    var carton_sum = parseFloat($('#carton_quantity_total').text());
                    var cbm_sum = parseFloat($('#cbm_total').text());
                    var weight_sum = parseFloat($('#weight_total').text());
                    carton_sum += parseFloat(carton_cell_value);
                    cbm_sum += parseFloat(cbm_cell_value);
                    weight_sum += parseFloat(weight_cell_value);
                    $('#carton_quantity_total').text(carton_sum);
                    $('#cbm_total').text(cbm_sum);
                    $('#weight_total').text(weight_sum);

              }else{
                  $(this).parent().parent().parent().removeClass('selected');
                    var carton_cell_value = $(this).parent().parent().parent().find('td div[data-item="carton"]').data('item-carton-qty');
                    var cbm_cell_value = $(this).parent().parent().parent().find('td div[data-item="cbm"]').data('item-cbm');
                    var weight_cell_value = $(this).parent().parent().parent().find('td div[data-item="weight"]').data('item-weight');
                    var carton_sum = parseFloat($('#carton_quantity_total').text());
                    var cbm_sum = parseFloat($('#cbm_total').text());
                    var weight_sum = parseFloat($('#weight_total').text());
                    carton_sum -= parseFloat(carton_cell_value);
                    cbm_sum -= parseFloat(cbm_cell_value);
                    weight_sum -= parseFloat(weight_cell_value);
                    $('#carton_quantity_total').text(carton_sum);
                    $('#cbm_total').text(cbm_sum);
                    $('#weight_total').text(weight_sum);

              }
            });
        });
</script>
@endsection
