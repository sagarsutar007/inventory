@extends('adminlte::page')

@section('title', 'Inventory')

@section('content_header')
    <h1 class="m-0 text-dark">Hi {{ Auth::user()->name }}!</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 connectedSortable ui-sortable">
            
        </div>
        <div class="col-md-4 connectedSortable ui-sortable">
            <div class="card direct-chat direct-chat-primary">
                <div class="card-header ui-sortable-handle" style="cursor: move;">
                    <h3 class="card-title">Notification</h3>
                </div>
                <div class="card-body">
                    <div class="direct-chat-messages">
                        <div class="direct-chat-msg">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-left">Alexander Pierce</span>
                                <span class="direct-chat-timestamp float-right">23 Jan 2:00 pm</span>
                            </div>
                            <img class="direct-chat-img" src="//via.placeholder.com/128x128" alt="message user image">
                            <div class="direct-chat-text">
                                Is this template really for free? That's unbelievable!
                            </div>
                        </div>
                        <div class="direct-chat-msg right">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-right">Sarah Bullock</span>
                                <span class="direct-chat-timestamp float-left">23 Jan 2:05 pm</span>
                            </div>
                            <img class="direct-chat-img" src="//via.placeholder.com/128x128/f8d5e3" alt="message user image">
                            <div class="direct-chat-text">
                                You better believe it!
                            </div>
                        </div>
                        <div class="direct-chat-msg">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-left">Alexander Pierce</span>
                                <span class="direct-chat-timestamp float-right">23 Jan 5:37 pm</span>
                            </div>
                            <img class="direct-chat-img" src="//via.placeholder.com/128x128" alt="message user image">
                            <div class="direct-chat-text">
                                Working with AdminLTE on a great new app! Wanna join?
                            </div>
                        </div>
                        <div class="direct-chat-msg right">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-right">Sarah Bullock</span>
                                <span class="direct-chat-timestamp float-left">23 Jan 6:10 pm</span>
                            </div>
                            <img class="direct-chat-img" src="//via.placeholder.com/128x128/f8d5e3" alt="message user image">
                            <div class="direct-chat-text">
                                I would love to.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <form action="#" method="post">
                        <div class="input-group">
                            <input type="text" name="message" placeholder="Type Message ..." class="form-control">
                            <span class="input-group-append">
                            <button type="button" class="btn btn-primary">Send</button>
                            </span>
                        </div>
                    </form>
                </div>
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
        })
    </script>
@stop