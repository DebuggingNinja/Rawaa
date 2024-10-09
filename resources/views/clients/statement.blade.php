<!-- resources/views/statement.blade.php -->

@extends('layout.app')

@section('title', $title)
@section('description', $description)

@section('style')
<style>
  /* Add your custom styles here */
  .horizontal-table table {
    width: 100%;
    border-collapse: collapse;
  }

  .horizontal-table table tbody tr:nth-child(even) {
    background-color: #f8f9fb;
  }

  .horizontal-table table th,
  .horizontal-table table td {
    padding: 8px;
  }

  @media print {
    body * {
      visibility: hidden;
    }

    body {
      background: white;
    }

    #pdf-content,
    #pdf-content * {
      visibility: visible;
    }

    #pdf-content {
      position: absolute !important;
      top: 0px;
      left: 0px
    }

    .contents {
      padding: 0px
    }
  }
</style>
@endsection

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="mb-3">
      <button class="btn btn-primary d-inline" onclick="printTable()">{{ __('statement.print') }}</button>
      <button class="btn btn-secondary d-inline" onclick="downloadPDF()">{{ __('statement.download_pdf') }}</button>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12 mb-30">
      <div class="card">
        <div class="card-header color-dark fw-500">
          {{ __('statement.client_statement') }}
        </div>
        <div class="card-body">
          <div class="userDatatable global-shadow border-light-0 w-100" id="pdf-content">
            <div class="my-3">
              <table class="table mb-0 table-borderless horizontal-table ">
                <thead>
                  <tr>
                    <th colspan="2">{{ __('statement.client_data') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>{{ __('statement.name') }}</strong> &nbsp; {{ $client->name }}</td>
                    <td><strong>{{ __('statement.code') }}</strong> &nbsp; {{ $client->code }}</td>
                  </tr>
                  <tr>
                    <td colspan="2"><strong>{{ __('statement.print_date') }}</strong> &nbsp; <span dir="ltr">{{
                        now()->format('d/m/Y h:i A') }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
              <form id="form_select_warehouse" action="{{route('clients.statement', $client->id)}}"
                    method="GET">
                <div class="row search-dates">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="start_date">
                        {{trans('repo.start_date')}}
                      </label>
                      <input class="form-control" type="date" name="start_date" id="start_date"
                             value="{{request()?->start_date ?? date('Y-m-d', strtotime("- 1 month"))}}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="end_date">
                        {{trans('repo.end_date')}}
                      </label>
                      <input class="form-control" type="date" name="end_date" id="end_date"
                             value="{{request()?->end_date ?? date('Y-m-d')}}">
                    </div>
                  </div>
                </div>
                <button class="btn btn-primary" type="submit">{{trans('repo.search')}}</button>
              </form>
            </div>
            <div class="table-responsive">
              <table class="table mb-0 table-borderless horizontal-table">
                <thead>
                  <tr class="userDatatable-header">
                    <th>
                      <span class="userDatatable-title">{{ __('expense.description') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ __('statement.operation_date') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ __('statement.client_debt') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ __('statement.client_payment') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ __('client.balance') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ __('statement.currency') }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($ledgers as $date => $ledger_items)
                    @foreach ($ledger_items as $ledger)
                    <tr>
                      <td>
                        <div class="userDatatable-inline-title" style=" text-wrap: balance; max-width:200px">
                          <h6>{{ $ledger['reason'] }}</h6>
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content {{ (app()->getLocale() == 'ar')?'text-start':'' }}" dir="ltr">
                          {{date("Y-m-d", strtotime($date))}}
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          <span class="text-danger">{{ $ledger['debit'] ?? 0}}</span>
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          <span class="text-success">{{ $ledger['credit'] ?? 0 }}</span>
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          @if($ledger['due'] > -1)
                            <span class="text-success">{{ $ledger['due'] }}</span>
                          @else
                            <span class="text-danger">{{ $ledger['due'] }}</span>
                          @endif
                        </div>
                      </td>
                      <td>
                        <div class="userDatatable-content">
                          {{ $ledger['currency']}}
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  @empty
                  <tr>
                    <td colspan="7">

                      <p class="text-center">{{ __('statement.no_data_found') }}</p>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="6">
                      <hr class="p-0 m-0">
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2">
                      <div class="userDatatable-content">
                        <span>{{ __('statement.client_balance') }} :</span>
                        <span class="mx-1 {{ $balance < 0 ?'text-danger':'text-success' }}" id="rmbBalance">
                          {{ $balance }}
                        </span>{{ __('statement.yuan') }}
                      </div>
                    </td>
                    <td colspan="2">
                      <div class="userDatatable-content">
                        <span>{{ __('statement.client_balance_usd') }} :</span>
                        <span class="mx-1 {{ $balance < 0 ?'text-danger':'text-success' }}" id="usdBalance"> --
                        </span>{{ __('statement.dollar') }}
                      </div>
                    </td>
                    <td colspan="2">
                      <input type="text" class="form-control d-print-none" id="conversion_rate" name="conversion_rate"
                        placeholder="{{ __('statement.enter_dollar_rate') }}">
                    </td>
                  </tr>
                </tfoot>
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
  function printTable() {
                                            window.print();
                                        }
                                          // Function to handle downloading PDF
                                           function downloadPDF() {
                                            var options = {
                                              margin: 10,
                                              filename: '{{ $client->name .'-'. date('Y-m-d') }}.pdf',
                                              image: { type: 'jpeg', quality: 0.98 },
                                              html2canvas: { scale: 2 },
                                              exclude: ['#conversion_rate'],
                                              jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                                            };
                                            html2pdf($('#pdf-content')[0],options);
                                        }
</script>
<script>
  // Function to update USD balance based on conversion rate
                                            function updateUSDBalance() {
                                                // Get RMB balance and conversion rate elements
                                                var rmbBalanceElement = $('#rmbBalance');
                                                var usdBalanceElement = $('#usdBalance');
                                                var conversionRateElement = $('#conversion_rate');

                                                // Get values from elements
                                                var rmbBalance = parseFloat(rmbBalanceElement.text());
                                                var conversionRate = parseFloat(conversionRateElement.val());

                                                // Check if the values are valid
                                                if (!isNaN(rmbBalance) && !isNaN(conversionRate)) {
                                                    // Calculate USD balance
                                                    var usdBalance = rmbBalance / conversionRate;

                                                    // Update USD balance element
                                                    usdBalanceElement.text(usdBalance.toFixed(2));
                                                }
                                            }

                                            // Attach the update function to the conversion rate input's change event
                                            $('#conversion_rate').on('input', updateUSDBalance);

                                            // Initial update when the page loads
                                            updateUSDBalance();
</script>
@endsection
