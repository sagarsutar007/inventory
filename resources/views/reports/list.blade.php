@extends('adminlte::page')

@section('title', 'Reports')

@section('content')
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card mt-3">
                <div class="card-header">
                  <h3 class="card-title"><i class="fas fa-clipboard-list"></i>&nbsp;Reports</h3> 
                </div>
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="{{ route('reports.rmPriceList') }}">RM Price List</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="{{ route('reports.materialList') }}">Material Master List</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="{{ route('reports.bom') }}">Bill of Material View</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="{{ route('reports.bomCost') }}">Bill of Material Cost View</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="#">Finished Good Cost Summary</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="#">Raw Material Stock Report</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="#">Production Order Report</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="#">Production Order Shortage Report</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="#">Production Order Shortage Report Consolidated</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="#">Planned Order Shortage Report</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="#">Planned Order Shortage Report Consolidated</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="{{ route('reports.rmPurchaseReport') }}">Raw Material Purchase Report</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <a href="#">Raw Material Issuance Report</a>
                    <i class="fas fa-chevron-right"></i>
                  </li>
                </ul>
              </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        
    </script>
@stop