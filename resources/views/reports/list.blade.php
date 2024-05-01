@extends('adminlte::page')

@section('title', 'Reports')

@section('content')
    <div class="row equal-height-row">
      <div class="col-md-4">
        <div class="card bg-gradient-warning mt-3">
          <div class="card-header">
            <h3 class="card-title">Raw Material</h3>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex align-items-center justify-content-between bg-warning">
                <a href="{{ route('reports.rmPriceList') }}">RM Price List</a>
                <i class="fas fa-chevron-right"></i>
              </li>
              <li class="list-group-item d-flex align-items-center justify-content-between bg-warning">
                <a href="{{ route('reports.materialList') }}">Material Master List</a>
                <i class="fas fa-chevron-right"></i>
              </li>
              <li class="list-group-item d-flex align-items-center justify-content-between bg-warning">
                <a href="{{ route('reports.stockReport') }}">Raw Material Stock Report</a>
                <i class="fas fa-chevron-right"></i>
              </li>
              <li class="list-group-item d-flex align-items-center justify-content-between bg-warning">
                <a href="{{ route('reports.rmPurchaseReport') }}">Raw Material Purchase Report</a>
                <i class="fas fa-chevron-right"></i>
              </li>
              <li class="list-group-item d-flex align-items-center justify-content-between bg-warning">
                <a href="{{ route('reports.rmIssuanceReport') }}">Raw Material Issuance Report</a>
                <i class="fas fa-chevron-right"></i>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card bg-gradient-info mt-3">
          <div class="card-header">
            <h3 class="card-title">Bill of Material</h3>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex align-items-center justify-content-between bg-info">
                <a href="{{ route('reports.bom') }}">Bill of Material View</a>
                <i class="fas fa-chevron-right"></i>
              </li>
              <li class="list-group-item d-flex align-items-center justify-content-between bg-info">
                <a href="{{ route('reports.bomCost') }}">Bill of Material Cost View</a>
                <i class="fas fa-chevron-right"></i>
              </li>
              <li class="list-group-item d-flex align-items-center justify-content-between bg-info">
                <a href="{{ route('reports.fgCostSummary') }}">Finished Good Cost Summary</a>
                <i class="fas fa-chevron-right"></i>
              </li>
            </ul>
          </div>
        </div>
      </div>
        <div class="col-md-4 mx-auto">
            <div class="card mt-3 bg-gradient-success">
                <div class="card-header">
                  <h3 class="card-title">Production Reports</h3> 
                </div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center justify-content-between bg-success">
                      <a href="{{ route('reports.poReport') }}">Production Order Report</a>
                      <i class="fas fa-chevron-right"></i>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between bg-success">
                      <a href="{{ route('reports.poShortageReport') }}">Production Order Shortage Report</a>
                      <i class="fas fa-chevron-right"></i>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between bg-success">
                      <a href="{{ route('reports.poConsolidatedShortageReport') }}">Production Order Shortage Report Consolidated</a>
                      <i class="fas fa-chevron-right"></i>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between bg-success">
                      <a href="{{ route('reports.ploShortageReport') }}">Planned Order Shortage Report</a>
                      <i class="fas fa-chevron-right"></i>
                    </li>
                    {{-- <li class="list-group-item d-flex align-items-center justify-content-between">
                      <a href="#">Planned Order Shortage Report Consolidated</a>
                      <i class="fas fa-chevron-right"></i>
                    </li> --}}
                    
                  </ul>
                </div>
              </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        
    </script>
@stop