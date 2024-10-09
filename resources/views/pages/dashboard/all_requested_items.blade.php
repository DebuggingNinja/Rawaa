@section('title', $title)
@extends('layout.app')
@section('content')
<div class="crm mb-25">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <button id="print-items" class="btn btn-primary">print</button>
      </div>

      <div class="card-body p-0 ">
        <div class="table-responsive print-content">
          <table class="table table--default table-borderless">
            <thead>
            <tr>
              <th>{{ trans('order.items') }}</th>
              <th>{{trans('order.check_date')}}</th>
              <th>{{trans('client.clients')}}</th>
              <th>{{trans('order.supplier')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($requestedItems as $requestedItem)
              <tr>
                <td>
                  <a href="{{route('buy_ship_orders.edit',$requestedItem->order?->id)}}" target="__blank">
                    {{$requestedItem?->carton_code . " - " . $requestedItem?->item}}
                  </a>
                </td>
                <td>{{$requestedItem?->check_date}}</td>
                <td>
                  <a href="{{route('clients.show',$requestedItem->order?->client?->id)}}">
                    {{$requestedItem->order?->client?->name}} - {{ $requestedItem->order?->client?->code }}
                  </a>
                </td>
                <td>{{$requestedItem->supplier?->code . " - " . $requestedItem->supplier?->name}}</td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
  <script>
    $(document).ready(function () {

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

    })
  </script>
@endsection
