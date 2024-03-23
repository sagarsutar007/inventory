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
                  <li class="list-group-item">
                    <a href="{{ route('reports.rmPriceList') }}">RM Price List</a>
                  </li>
                  <li class="list-group-item">A second item</li>
                  <li class="list-group-item">A third item</li>
                </ul>
              </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        
    </script>
@stop