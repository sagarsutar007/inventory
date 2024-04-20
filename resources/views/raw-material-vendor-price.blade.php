@extends('adminlte::page')

@section('title', 'Raw Materials Vendors Price List')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Raw Materials Vendors Price List</h3>
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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $index => $item)
                                    <tr>
                                        <td>{{ ($page - 1) * $length + $index + 1 }}</td>
                                        <td>{{ $item['part_code'] }}</td>
                                        <td>{{ $item['description'] }}</td>
                                        <td>{{ $item['commodity'] }}</td>
                                        <td>{{ $item['category'] }}</td>
                                        <td>{{ $item['uom_shortcode'] }}</td>
                                        <td>{{ $item['make'] }}</td>
                                        <td>{{ $item['mpn'] }}</td>
                                        @for ($i=1; $i<=$columnsCount; $i++)
                                        <td>{{ $item['vendor_' . $i]??'' }}</td>
                                        <td>{{ $item['price_' . $i]??'' }}</td>
                                        @endfor
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
        });
    </script>
@stop