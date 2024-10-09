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
          {{ __('statement.company_statement') }}
        </div>
        <div class="card-body">
          <div class="userDatatable global-shadow border-light-0 w-100" id="pdf-content">
            <div class="my-3">
              <table class="table mb-0 table-borderless horizontal-table ">
                <thead>
                  <tr>
                    <th colspan="2">{{ __('statement.company_data') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>{{ __('statement.name') }}</strong> &nbsp; {{ $company->name }}</td>
                    <td><strong>{{ __('statement.code') }}</strong> &nbsp; {{ $company->code }}</td>
                  </tr>
                  <tr>
                    <td colspan="2"><strong>{{ __('statement.print_date') }}</strong> &nbsp; <span dir="ltr">{{
                        now()->format('d/m/Y h:i A') }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
              <form id="form_select_warehouse" action="{{route('shipping_companies.statement', $company->id)}}" method="GET">
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
                      <span class="userDatatable-title">{{ __('statement.company_debt') }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ __('statement.company_payment') . " " . trans("statement.yuan") }}</span>
                    </th>
                    <th>
                      <span class="userDatatable-title">{{ __('statement.company_payment') . " " . trans("statement.dollar") }}</span>
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
                          {{$date}}
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
                          <span class="text-success">{{ $ledger['credit_dollar'] ?? 0 }}</span>
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
                    <td colspan="5">
                      <hr class="p-0 m-0">
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3">
                      <div class="userDatatable-content">
                        <span>{{ __('statement.company_balance') }} :</span>
                      </div>
                    </td>

                    <td colspan="1">
                      <div class="userDatatable-content">
                        <span class="mx-1 {{ $balance<0?'text-danger':'text-success' }}" id="rmbBalance">
                          {{ $balance }}
                        </span>{{ __('statement.yuan') }}
                      </div>
                    </td>

                    <td colspan="1">
                      <div class="userDatatable-content">
                        <span class="mx-1 {{ $dollar_balance<0?'text-danger':'text-success' }}" id="rmbBalance">
                          {{ $dollar_balance }}
                        </span>{{ __('statement.dollar') }}
                      </div>
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
                                              filename: '{{ $company->name .'-'. date('Y-m-d') }}.pdf',
                                              image: { type: 'jpeg', quality: 0.98 },
                                              html2canvas: { scale: 2 },
                                              exclude: ['#conversion_rate'],
                                              jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                                            };
                                            html2pdf($('#pdf-content')[0],options);
                                        }
</script>

@endsection
