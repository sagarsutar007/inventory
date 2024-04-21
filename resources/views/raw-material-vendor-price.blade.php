@extends('adminlte::page')

@section('title', 'Raw Materials Vendor Price List')

@section('content')
    <div class="row">
        <div class="col-md-12 mt-3">
            <form action="" id="material-search" method="get">
            <div class="card ">
                <div class="card-header">
                    <h3 class="card-title">Materials Vendor Price Filter</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="coll-btn" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md mb-3">
                            <input type="text" id="part_code" name="part_code" class="form-control" placeholder="Partcode" value="{{ $_GET['part_code']??''; }}">
                        </div>
                        <div class="col-md mb-3">
                            <input type="text" id="description" name="description" class="form-control" placeholder="Description" value="{{ $_GET['description']??''; }}">
                        </div>
                        <div class="col-md mb-3">
                            <input type="text" id="make" name="make" class="form-control" placeholder="Make" value="{{ $_GET['make']??''; }}">
                        </div>
                        <div class="col-md mb-3">
                            <input type="text" id="mpn" name="mpn" class="form-control" placeholder="MPN" value="{{ $_GET['mpn']??''; }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md mb-3">
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <select class="form-control select2" id="uom" name="uom_id[]" multiple="multiple" style="width: 100%;">
                                        <option value="all" {{ isset($_GET['uom_id']) && in_array('all', $_GET['uom_id']) ? 'selected' : '' }}>All</option>
                                        @foreach($uom as $unit)
                                        <option value="{{$unit->uom_id}}" {{ isset($_GET['uom_id']) && in_array($unit->uom_id, $_GET['uom_id']) ? 'selected' : '' }}>
                                            {{$unit->uom_text}}
                                        </option>
                                        @endforeach
                                    </select>                                        
                                </div>
                            </div>
                        </div>
                        <div class="col-md mb-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <select class="form-control select2" id="commodity" name="commodity_id[]" multiple="multiple" style="width: 100%;">
                                        <option value="all" {{ isset($_GET['commodity_id']) && in_array('all', $_GET['commodity_id']) ? 'selected' : '' }}>All</option>
                                        @foreach($commodity as $cmdty)
                                        <option value="{{$cmdty->commodity_id}}" {{ isset($_GET['commodity_id']) && in_array($cmdty->commodity_id, $_GET['commodity_id']) ? 'selected' : '' }}>
                                            {{$cmdty->commodity_name}}
                                        </option>
                                        @endforeach 
                                    </select>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md mb-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <select class="form-control select2" id="category" name="category_id[]" multiple="multiple" style="width: 100%;">
                                        <option value="all" {{ isset($_GET['category_id']) && in_array('all', $_GET['category_id']) ? 'selected' : '' }}>All</option>
                                        @foreach($category as $ctg)
                                        <option value="{{$ctg->category_id}}" {{ isset($_GET['category_id']) && in_array($ctg->category_id, $_GET['category_id']) ? 'selected' : '' }}>
                                            {{$ctg->category_name}}
                                        </option>
                                        @endforeach  
                                    </select>                                        
                                </div>
                            </div>
                        </div>
                        <div class="col-md mb-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <select class="form-control select2" id="dependent" name="dm_id[]" multiple="multiple" style="width: 100%;">
                                        <option value="all" {{ isset($_GET['dm_id']) && in_array('all', $_GET['dm_id']) ? 'selected' : '' }}>All</option>
                                        @foreach($dependents as $dpn)
                                        <option value="{{$dpn->dm_id}}" {{ isset($_GET['dm_id']) && in_array($dpn->dm_id, $_GET['dm_id']) ? 'selected' : '' }}>
                                            {{ $dpn->description . " - " . $dpn->frequency }}
                                        </option>
                                        @endforeach  
                                    </select>                                        
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 mb-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <select class="form-control select2" id="vendors-dd" name="vendor_id[]" multiple="multiple" style="width: 100%;">
                                        <option value="all" {{ isset($_GET['vendor_id']) && in_array('all', $_GET['vendor_id']) ? 'selected' : '' }}>All</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{$vendor->vendor_id}}" {{ isset($_GET['vendor_id']) && in_array($vendor->vendor_id, $_GET['vendor_id']) ? 'selected' : '' }}>
                                            {{ $vendor->vendor_code . " - " . $vendor->vendor_name }}
                                        </option>
                                        @endforeach  
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Materials Vendor Price List</h3>
                    <div class="card-tools">
                        @can('import-raw-vendor-price')
                        <a class="btn btn-light btn-sm" href="{{ route('raw.bulkPrice') }}"><i class="fas fa-file-import text-secondary"></i> Bulk Upload Price List</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div style="display: inline-block;width: 100%;">
                        <table id="rawmaterials" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr id="material-header">
                                    <th>Sno.</th>
                                    <th>Part Code</th>
                                    <th>Description</th>
                                    <th>Commodity</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th>Make</th>
                                    <th>MPN</th>
                                    @for ($i=1; $i<=$columnsCount; $i++)
                                    <th>Vendor {{ $i }}</th>
                                    <th>Price {{ $i }}</th>
                                    @endfor
                                    <th>Dependent</th>
                                    <th>Frequency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $index => $item)
                                    <tr>
                                        <td>{{ ($page - 1) * $length + $index + 1 }}</td>
                                        <td>
                                            <a href="#" role="button" data-matid="{{ $item['material_id'] }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalView">
                                                {{ $item['part_code'] }}
                                            </a>
                                        </td>
                                        <td>{{ $item['description'] }}</td>
                                        <td>{{ $item['commodity'] }}</td>
                                        <td>{{ $item['category'] }}</td>
                                        <td class="text-center">{{ $item['uom_shortcode'] }}</td>
                                        <td>{{ $item['make'] }}</td>
                                        <td>{{ $item['mpn'] }}</td>
                                        @for ($i=1; $i<=$columnsCount; $i++)
                                        <td>{{ $item['vendor_' . $i]??'' }}</td>
                                        <td><div class='text-right'>{{ $item['price_' . $i]??'' }}</div></td>
                                        @endfor
                                        <td>{{ $item['dependent'] }}</td>
                                        <td>{{ $item['frequency'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    @if (isset($materials))
                    {{ $materials->links() }}
                    @endif
                    
                </div>
            </div>
        </div>
    </div>

    <x-modaltabs id="modalView" title="View Raw Material">
        <x-slot name="header">
            <ul class="nav nav-pills nav-fill">
                <li class="nav-item">
                    <a class="nav-link active" id="overview-tab" data-toggle="tab" data-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="documents-tab" data-toggle="tab" data-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="true">Documents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="vendors-tab" data-toggle="tab" data-target="#vendors" type="button" role="tab" aria-controls="vendors" aria-selected="true">Vendors</a>
                </li>
            </ul>
        </x-slot>
        <x-slot name="body">
            <div class="tab-content" id="view-material-modal">
                
            </div>
        </x-slot>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </x-slot>
    </x-modaltabs>
@stop

@section('js')
    <script>
        $(function () {
            var currentUserName = "{{ auth()->user()->name }}";
            var userStamp = userTimeStamp(currentUserName);
            var safeStamp = $(userStamp).text();

            var urlParams = new URLSearchParams(window.location.search);
            var lengthParam = urlParams.get('length');
            var defaultLength = 10;

            var table = $('#rawmaterials').DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": true,
                "info": false,
                "stateSave": true,
                "buttons": [
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        },
                        messageBottom: safeStamp,
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        },
                        messageBottom: safeStamp,
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        },
                        messageBottom: userStamp,
                    },
                    'colvis',
                ],
                "lengthMenu": datatableLength,
                "searching": false,
                "ordering": true,
                "columnDefs": [
                    {
                        "targets": [0],
                        "orderable": false
                    },
                    {
                        "targets": [0, 3, 4, 6, 7, -1, -2],
                        "visible": false
                    }
                ],
                "dom": 'lBfrt',
                "language": {
                    "lengthMenu": "_MENU_"
                },
                "pageLength": lengthParam ? parseInt(lengthParam) : defaultLength
            });

            table.on('length.dt', function (e, settings, len) {
                var currentUrl = window.location.href;
                var url = new URL(currentUrl);
                var params = new URLSearchParams(url.search);
                params.set('length', len);
                var newUrl = url.origin + url.pathname + '?' + params.toString();
                window.location.href = newUrl;
            });

            $('.pagination a').on('click', function(e) {
                e.preventDefault();
                var pageUrl = $(this).attr('href');
                var currentUrl = window.location.href;
                var baseUrl = currentUrl.split('?')[0];
                var url = new URL(baseUrl);
                var currentUrlParams = new URLSearchParams(window.location.search);
                currentUrlParams.forEach(function(value, key) {
                    if (key !== 'page') {
                        url.searchParams.set(key, value);
                    }
                });
                var pageParam = new URLSearchParams(pageUrl.split('?')[1]).get('page');
                url.searchParams.set('page', pageParam);
                
                window.location.href = url;
            });    

            $('#uom').select2({
                theme: 'bootstrap4',
                allowClear: true,
                placeholder: "Unit"
            });

            $('.select2').on('change', function() {
                var selectedValues = $(this).val();

                // If "All" option is selected, deselect all other options
                if (selectedValues && selectedValues.includes('all')) {
                    // Deselect all options except "All"
                    $(this).val(['all']).trigger('change.select2'); // Triggering 'change.select2' prevents infinite loop
                }
            });

            $('#category').select2({
                theme: 'bootstrap4',
                allowClear: true,
                placeholder: "Categories"
            });

            $('#commodity').select2({
                theme: 'bootstrap4',
                allowClear: true,
                placeholder: "Commodity"
            });
            
            $('#dependent').select2({
                theme: 'bootstrap4',
                allowClear: true,
                placeholder: "Dependent Material"
            });

            $('#vendors-dd').select2({
                theme: 'bootstrap4',
                allowClear: true,
                placeholder: "Vendors"
            });

            $('#modalView').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var materialId = button.data('matid');
                var modal = $(this)
                modal.find(".nav-link").removeClass('active');
                modal.find(".nav-link:first").addClass('active');
                $.ajax({
                    url: '/app/raw-materials/' + materialId + '/show',
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $("#view-material-modal").html(response.html);
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $("#coll-btn").trigger('click');
        });
    </script>
@stop