@extends('adminlte::page')

@section('title', 'Commodities')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Commodities</h3>
                    <div class="card-tools">
                        @can('add-commodity')
                        <a class="btn btn-light btn-sm" href="{{ route('commodities.add') }}"><i class="fa fa-plus text-secondary"></i> Multiple Records</a>
                        <a class="btn btn-light btn-sm" href="{{ route('commodities.bulk') }}"><i class="fas fa-file-import text-secondary"></i> Bulk Upload</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table id="commodities" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <!-- <th width="5%">Sno.</th> -->
                                <th width="10%">Code</th>
                                <th>Commodity</th>
                                <th>RM</th>
                                <th>SFG</th>
                                <th>FG</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($commodities as $commodity)
                            <tr>
                                <!-- <td width="5%">{{ $loop->iteration }}</td> -->
                                <td width="10%">{{ $commodity->commodity_number }}</td>
                                <td>{{ $commodity->commodity_name }}</td>
                                <td>{{ $commodity->raw_count }}</td>
                                <td>{{ $commodity->semi_finished_count }}</td>
                                <td>{{ $commodity->finished_count }}</td>
                                <td width="10%">
                                    @can('edit-commodity')
                                    <a href="#" role="button" data-comid="{{ $commodity->commodity_id }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit" >
                                        <i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit Commodity"></i>
                                    </a>
                                    @endcan 
                                    @can('delete-commodity') / <form action="{{ route('commodities.destroy', $commodity->commodity_id) }}" method="post" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Are you sure you want to delete this record?')"><i class="fas fa-trash" data-toggle="tooltip" data-placement="top" title="Delete Commodity"></i></button>
                                    </form>
                                    @endcan 
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-adminlte-modal id="modalEdit" title="Edit Commodity" icon="fas fa-edit">
        <div class="row">
            <div class="col-12">
                <input type="hidden" id="edit-commodity-id" value="">
                <input type="hidden" id="old-commodity-name" value="">
                <div class="form-group">
                    <label for="edit-commodity">Enter Commodity Name</label>
                    <input type="text" id="edit-commodity" name="commodity_name" class="form-control" placeholder="Loading..." value="">
                </div>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-commodity" theme="outline-primary" label="Save"/>
        </x-slot>
    </x-adminlte-modal>
@stop


@section('js')
    <script>
        $(function () {
            $("#commodities").DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": true,
                "paging": true,
                "info": true,
                "language": {
                    "lengthMenu": "_MENU_"
                },
                "lengthMenu": datatableLength,
                "buttons": [
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            columns: ':visible:not(.exclude)'
                        }
                    },
                    'colvis',
                ],
                "stateSave": true,
            }).buttons().container().appendTo('#commodities_wrapper .col-md-6:eq(0)');


            $('#modalEdit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var commodityId = button.data('comid');
                var modal = $(this)
                
                $.ajax({
                    url: '/app/commodities/' + commodityId + '/edit',
                    method: 'GET',
                    success: function(response) {
                        if (response.commodity) {
                            modal.find('.modal-body input#edit-commodity-id').val(response.commodity.commodity_id);
                            modal.find('.modal-body input#edit-commodity').val(response.commodity.commodity_name);
                            modal.find('.modal-body input#old-commodity-name').val(response.commodity.commodity_name);
                        } else {
                            toastr.error('Commodity not found!');
                        }
                        modal.find('.modal-body input#edit-commodity').attr("placeholder", "Enter commodity name");
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            })

            $(document).on('click', '.btn-save-commodity', function(e) {
                e.preventDefault();
                
                let comm_name = $("#edit-commodity").val();
                let comm_id = $("#edit-commodity-id").val();
                let old_comm_name = $("#old-commodity-name").val();
                
                if (comm_name === "") {
                    toastr.error('Please enter commodity name!');
                } else {
                    if (comm_id === "") {
                        toastr.error('Something went wrong! Please reload the page.');
                    } else {
                        $.ajax({
                            url: '/app/commodities/' + comm_id + '/update',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                commodity_name: comm_name
                            },
                            success: function(response) {
                                $('td:contains("'+old_comm_name+'")').text(comm_name);
                                toastr.success('Commodity updated successfully');
                            },
                            error: function(error) {
                                console.error(error);
                                if (error.responseJSON && error.responseJSON.message) {
                                    toastr.error(error.responseJSON.message);
                                }
                            }
                        });
                    }
                }

                $('#modalEdit').modal('hide');
            });

            // Show Error Messages
            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif

            // Show Success Message
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif

        });
    </script>
@stop