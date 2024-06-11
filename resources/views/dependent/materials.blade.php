@extends('adminlte::page')

@section('title', 'Dependent Materials')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Dependent Materials</h3>
                    <div class="card-tools">
                        <a class="btn btn-light btn-sm" href="{{ route('dm.add') }}">
                            <i class="fa fa-plus text-secondary"></i> Add Records
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="dependents" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Frequency</th>
                                <th>RM Count</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($dependents as $dependent)
                            <tr>
                                <td>{{ $dependent->description }}</td>
                                <td>{{ $dependent->frequency }}</td>
                                <td>{{ $dependent->raw_count }}</td>
                                <td width="10%">
                                    @can('edit-dependent-material')
                                    <button data-dmid="{{ $dependent->dm_id }}" data-descr="{{ $dependent->description }}" data-freq="{{ $dependent->frequency }}" class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalEdit">
                                        <i class="fas fa-edit" data-toggle="tooltip" data-placement="top" title="Edit"></i>
                                    </button> @endcan 
                                    @can('edit-dependent-material') / 
                                    <form action="{{ route('dm.destroy', $dependent->dm_id) }}" method="post" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link p-0" onclick="return confirm('Are you sure you want to delete this record?')">
                                            <i class="fas fa-trash text-danger" data-toggle="tooltip" data-placement="top" title="Delete Dependent Material"></i>
                                        </button>
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

    <x-adminlte-modal id="modalEdit" title="Edit Dependent Material" icon="fas fa-edit">
        <div class="row">
            <div class="col-12">
                <input type="hidden" name="dm_id" id="dm_id" value="">
                <input type="hidden" id="old_description" value="">
                <input type="hidden" id="old_frequency" value="">
                <div class="form-group">
                    <label for="description">Enter Description</label>
                    <input type="text" id="description" name="description" class="form-control" placeholder="Loading..." value="">
                </div>
                <div class="form-group">
                    <label for="frequency">Select Frequency</label>
                    <select id="frequency" name="frequency" class="form-control">
                        <option value="">-- Select Frequency --</option>
                        <option value="Daily">Daily</option>
                        <option value="Weekly">Weekly</option>
                        <option value="Bi-weekly">Bi-weekly</option>
                        <option value="Monthly">Monthly</option>
                        <option value="Bi-monthly">Bi-monthly</option>
                        <option value="Quarterly">Quarterly</option>
                        <option value="Half-yearly">Half-yearly</option>
                        <option value="Yearly">Yearly</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="btn-sm" theme="default" label="Close" data-dismiss="modal"/>
            <x-adminlte-button class="btn-sm btn-save-dependent-material" theme="primary" label="Save"/>
        </x-slot>
    </x-adminlte-modal>
@stop


@section('js')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();

            $("#dependents").DataTable({
                "responsive": true,
                "lengthChange": true,
                "autoWidth": true,
                "paging": true,
                "info": true,
                "language": {
                    "lengthMenu": "_MENU_"
                },
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
                "stateSaveParams": function(settings, data) {
                    data.search.search = '';
                },
                "lengthMenu": datatableLength,
            }).buttons().container().appendTo('#dependents_wrapper .col-md-6:eq(0)');

            $('#modalEdit').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var dmId = button.data('dmid');
                var descr = button.data('descr');
                var freq = button.data('freq');
                var modal = $(this);

                modal.find('.modal-body input#dm_id').val(dmId);
                modal.find('.modal-body input#old_description').val(descr);
                modal.find('.modal-body input#old_frequency').val(freq);
                modal.find('.modal-body input#description').val(descr);
                modal.find('.modal-body select#frequency').val(freq);
                
            });

            $(document).on('click', '.btn-save-dependent-material', function(e) {
                e.preventDefault();

                let dm_id = $("#dm_id").val();
                let description = $("#description").val();
                let frequency = $("#frequency").val();
                
                if (description === "") {
                    toastr.error('Please enter description!');
                } else if (frequency === "") {
                    toastr.error('Please select frequency!');
                } else  {
                    if (dm_id === "") {
                        toastr.error('Something went wrong! Please reload the page.');
                    } else {
                        $.ajax({
                            url: '/app/dependent-materials/' + dm_id + '/update',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                description: description,
                                frequency: frequency,
                            },
                            success: function(response) {
                                toastr.success('Record updated successfully!');
                                // window.location.reload();
                                console.log(response);
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