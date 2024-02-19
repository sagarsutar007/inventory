@extends('adminlte::page')

@section('title', 'Bill of Materials')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Bill of Materials</h3>
                </div>
                <div class="card-body">
                    <table id="materials" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <!-- <th width="5%">Sno.</th> -->
                                <th width="10%">Code</th>
                                <th>Material Name</th>
                                <th>Unit</th>
                                <th>Commodity</th>
                                <th>Category</th>
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

    <x-adminlte-modal id="modalView" title="View Material" icon="fas fa-box" size="lg" scrollable>
        <div class="row" id="view-material-modal">
            <div class="col-12">
                <h2 class="text-secondary text-center">Loading...</h2>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <x-adminlte-modal id="modalEdit" title="Edit Material" icon="fas fa-box" size='lg' scrollable>
        <form action="/" id="edit-material-form" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row" id="edit-material-modal">
                <div class="col-12">
                    <h2 class="text-secondary text-center">Loading...</h2>
                </div>
            </div>
            <x-slot name="footerSlot">
                <button type="button" class="btn btn-sm btn-outline-secondary add-raw-quantity-item">
                    <i class="fas fa-fw fa-plus"></i> Add BOM Item
                </button>
                <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
                <x-adminlte-button class="btn-sm btn-save-material" theme="outline-primary" label="Save"/>
            </x-slot>
        </form>
    </x-adminlte-modal>
@stop

@section('js')
    <script>
        $(function () {
            $('#materials').DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": true,
                "paging": true,
                "info": true,
                "buttons": [
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4]
                        }
                    }
                ],
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                "ajax": {
                    "url": "{{ route('bom.getBom') }}",
                    "type": "POST",
                    "data": function ( d ) {
                        d._token = '{{ csrf_token() }}';
                    }
                },
                "columns": [
                    // { "data": "sno", "name": "sno"},
                    { "data": "code", "name": "part_code" },
                    { "data": "material_name", "name": "description" },
                    { "data": "unit", "name": "uom_text" },
                    { "data": "commodity", "name": "commodity_name" },
                    { "data": "category", "name": "category_name" },
                    { "data": "action" },
                ],
                "lengthMenu": [10, 25, 50, 75, 100],
                "searching": true,
                "ordering": true,
                // "order": [[ 0, "asc" ]],
                "columnDefs": [
                    {
                        "targets": [5],
                        "orderable": false
                    }
                ],
                "dom": 'lBfrtip',
                "language": {
                    "lengthMenu": "_MENU_"
                },
            });

            // Show Error Messages
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error('{{ $error }}');
                @endforeach
            @endif

            // Show Success Message
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif

            $('#modalView').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var bomId = button.data('bomid');
                var materialPartcode = button.data('partcode');
                var materialDesc = button.data('desc');
                var modal = $(this);
                modal.find('.modal-title').text(materialDesc);
                $.ajax({
                    url: '/app/bill-of-materials/' + bomId + '/show',
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $("#view-material-modal").html(response.html);
                            $("#boms-table").DataTable({
                                "responsive": true,
                                "lengthChange": false,
                                "autoWidth": true,
                                "paging": false,
                                "searching": false,
                                "info": false,
                                "buttons": [
                                    {
                                        extend: 'excel',
                                        exportOptions: {
                                            columns: ':visible:not(.exclude)'
                                        },
                                        title: materialPartcode + " - " + materialDesc + " - BOM",
                                    },
                                    {
                                        extend: 'pdf',
                                        exportOptions: {
                                            columns: ':visible:not(.exclude)'
                                        },
                                        title: materialPartcode + " - " + materialDesc + " - BOM",
                                    },
                                    {
                                        extend: 'print',
                                        exportOptions: {
                                            columns: ':visible:not(.exclude)'
                                        },
                                        title: materialPartcode + " - " + materialDesc + " - BOM",
                                    },
                                    'colvis'
                                ],
                            }).buttons().container().appendTo('#export-section');
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('#modalEdit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var bomId = button.data('bomid');
                var modal = $(this)
                
                $.ajax({
                    url: '/app/bill-of-materials/' + bomId + '/edit',
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $("#edit-material-modal").html(response.html);

                            $('.raw-materials').each(function() {
                                initializeRawMaterialsSelect2($(this));
                            });
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            });

            function initializeRawMaterialsSelect2(selectElement) {
                selectElement.select2({
                    placeholder: 'Select Material',
                    theme: 'bootstrap4',
                    ajax: {
                        url: '{{ route("finished.getMaterials") }}',
                        dataType: 'json',
                        method: 'POST',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                _token: '{{ csrf_token() }}'

                            };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data, function (item) {
                                    return {
                                        id: item.material_id,
                                        text: item.description + "-" + item.part_code
                                    };
                                })
                            };
                        },
                        cache: true
                    }
                });
            }

            // Function to add a new raw material item
            $(document).on('click', ".add-raw-quantity-item", function () {
                var newItem = `
                    <div class="raw-with-quantity">
                        <div class="row">
                            <div class="col-md-8">
                                <select class="form-control raw-materials" name="raw[]" style="width:100%;">
                                    <option value=""></option>  
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control" name="quantity[]" placeholder="Quantity">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-times remove-raw-quantity-item"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>                            
                    </div>
                `;
                var $newItem = $(newItem);
                $(".raw-materials-container").append($newItem);
                
                var newSelect = $newItem.find('.raw-materials');
                initializeRawMaterialsSelect2(newSelect);
            });

            // Function to remove the closest raw material item
            $(document).on('click', '.remove-raw-quantity-item', function () {
                if ($('.raw-with-quantity').length > 1) {
                    $(this).closest('.raw-with-quantity').remove();
                } else {
                    alert("At least one Raw Material item should be present.");
                }
            });

            $('.btn-save-material').click(function () {
                var bomId = $("#bom-id").val();
                var formData = new FormData($('#edit-material-form')[0]);
                $.ajax({
                    url: '/app/bill-of-materials/' + bomId + '/update', 
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status) {
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                        var response = JSON.parse(xhr.responseText);
                        toastr.error(response.message);
                    }
                });

                $('#modalEdit').modal('hide');
            });
        });
    </script>
@stop