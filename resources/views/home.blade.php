@extends('adminlte::page')

@section('title', 'Inventory')

@section('content_header')
    <h1 class="m-0 text-dark">Hi {{ Auth::user()->name }}!</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 connectedSortable ui-sortable">
            {{-- counters --}}
            <div class="row">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $rawMaterialCount; ?></h3>
                            <p>Total Raw Materials</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Records <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h3><?= $semiMaterialCount; ?></h3>
                            <p>Total Semi Finished Materials</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Records <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-orange">
                        <div class="inner">
                            <h3><?= $finishedMaterialCount; ?></h3>
                            <p>Total Finished Materials</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Records <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $lowRawMaterialCount; ?></h3>
                            <p>Low Raw Materials</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Records<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $lowSemiMaterialCount; ?></h3>
                            <p>Low Semi Finished Materials</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Records<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= $lowFinishedMaterialCount; ?></h3>
                            <p>Low Finished Materials</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Records<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-white">
                        <div class="inner">
                            <h3><?= $zeroStockRawMaterialCount; ?></h3>
                            <p>Out of Stock Raw Materials</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Records<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?= $zeroStockSemiMaterialCount; ?></h3>
                            <p>Out of Stock Semi Finished Materials</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Records<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $zeroStockFinishedMaterialCount; ?></h3>
                            <p>Out of Stock Finished Materials</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Records<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 connectedSortable ui-sortable">
            <div class="card direct-chat direct-chat-primary">
                <div class="card-header ui-sortable-handle" style="cursor: move;">
                    <h3 class="card-title">Notification</h3>
                </div>
                <div class="card-body">
                    <div class="direct-chat-messages">
                        @foreach($notifications as $notification)
                            <div class="direct-chat-msg {{ $notification->user_id == Auth::id() ? '' : 'right' }}">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name {{ $notification->user_id == Auth::id() ? 'float-left' : 'float-right' }}">
                                        {{ $notification->user->name }}
                                    </span>
                                    <span class="direct-chat-timestamp {{ $notification->user_id == Auth::id() ? 'float-right' : 'float-left' }}">
                                        {{ $notification->created_at->format('d M h:i A') }}
                                    </span>
                                </div>
                                <img class="direct-chat-img" src="//via.placeholder.com/80x80/{{ $notification->user_id == Auth::id() ?'007bff/ffffff':'444444/ffffff' }}?text={{ $notification->user->name }}" alt="message user image">
                                <div class="direct-chat-text">
                                    {{ $notification->message }}
                                </div>
                            </div>
                        @endforeach
                    </div>                    
                </div>
                @can('send-notification')
                <div class="card-footer">
                    <form action="#" method="post" id="message-form" autocomplete="off">
                        @csrf
                        <div class="input-group">
                            <input type="text" id="msg-box" name="message" placeholder="Type Message ..." class="form-control">
                            <span class="input-group-append">
                                <button type="submit" class="btn btn-primary">Send</button>
                            </span>
                        </div>
                    </form>
                </div>
                @endcan
            </div>
        </div>
    </div>
@stop
@section('js')
    <script>
        $(function(){
            $('.connectedSortable').sortable({
                placeholder: 'sort-highlight',
                connectWith: '.connectedSortable',
                handle: '.card-header, .nav-tabs',
                forcePlaceholderSize: true,
                zIndex: 999999,
                revert: false,
                stop: function(event, ui) {
                    var newOrder = $(this).sortable('toArray');
                    localStorage.setItem('sortableOrder', JSON.stringify(newOrder));
                }
            });
            $('.connectedSortable .card-header').css('cursor','move');

            $('#message-form').on('submit', function(e){
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: "{{ route('save.chat.message') }}",
                    type: "POST",
                    data: formData,
                    success: function(response){
                        $("#msg-box").val('');
                        var chatMessages = $(".direct-chat-messages");
                        var newMessage = `
                            <div class="direct-chat-msg">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-left">${response.name}</span>
                                    <span class="direct-chat-timestamp float-right">${new Date().toLocaleString()}</span>
                                </div>
                                <img class="direct-chat-img" src="${response.picture}" alt="message user image">
                                <div class="direct-chat-text">${response.message}</div>
                            </div>
                        `;
                        chatMessages.append(newMessage);
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = JSON.parse(xhr.responseText);
                        toastr.error(errorMessage.message, 'Error');
                    }
                });
            });
        })
    </script>
@stop