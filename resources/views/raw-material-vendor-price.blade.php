@extends('adminlte::page')

@section('title', 'Raw Materials')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Raw Materials</h3>
                    <div class="card-tools">
                        @can('import-raw-vendor-price')
                        <a class="btn btn-light btn-sm" href="{{ route('raw.bulk') }}"><i class="fas fa-file-import text-secondary"></i> Bulk Upload Price List</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="rawmaterials" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th width="5%">Sno.</th>
                                    <th class="text-center">Image</th>
                                    <th width="10%">Partcode</th>
                                    <th>Raw Material Name</th>
                                    <th>Unit</th>
                                    <th>Commodity</th>
                                    <th>Category</th>
                                    <th>Make</th>
                                    <th>MPN</th>
                                    <th>Dependent Material</th>
                                    <th>Frequency</th>
                                    <th>Re Order</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function () {

        });
    </script>
@stop