@extends('layouts.app', ['title' => ($title) ?? 'Tous les utilisateurs'])

@section('content')
<div class="container">
    <div id="snippetContent">
        <main class="content">
            <div class="container p-0">
                <div class="card">
                    <div class="row" id="users_connected">
                        <div class="col-lg-12">
                            <h4 class="m-3"><u>{{ $title }}</u></h4>
                        </div>
                        @foreach ($users as $user)
                        <div class="user col-lg-3 col-md-2" id="card_user_{{ $user->id }}">
                            <a href="{{ route('home', ['id' => $user->id]) }}" class="list-group-item list-group-item-action border-0">
                                @if ($user->unread_messages != 0)
                                <div class="badge bg-success float-right" style="color: white">
                                    {{ $user->unread_messages }}
                                </div>
                                @endif
                                <div class="d-flex align-items-start">
                                    <img src="https://ui-avatars.com/api/?name={{ $user->name }}" class="rounded-circle mr-1" alt="{{ $user->name }}" width="40" height="40" />
                                    <div class="flex-grow-1 ml-3">
                                        {{ $user->name }}
                                        <div class="small">
                                            @if ($user->is_online == true)
                                                <span class="fa fa-circle chat-online"></span> Online
                                            @else
                                                <span class="fa fa-circle chat-offline"></span> Offline
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets_custom/js/socket-io.js') }}"></script>
<script>
    var init = function() {
        console.log('!! init');
        /*
        $('.chat-messages').animate({scrollTop: $('.chat-messages').prop('scrollHeight')}, 'fast');
        $('.chat-messages').attr('scroll-top', 100);
        */
    }

    var users_connected = function () {
        $.ajax({
            type: 'get',
            url: "{{ route('ajax.users_connected') }}",
            success: function(result) {
                $('#users_connected').find('.user').remove();
                $('#users_connected').append(result);
            }
        });
    }

    var count_messages = function (user_id) {
        $.ajax({
            type: 'get',
            url: "{{ route('ajax.count_messages') }}",
            data: {user_id:user_id},
            success: function(output) {
                var card = $('#card_user_' + user_id).find('.badge');
                card.html(output);
            }
        });
    }

    init();


    $(function() {

        var user_id = "{{ Auth::id() }}";
        var socket = io('http://localhost:3000', {
            query: {
                user_id: user_id
            }
        });

        $('#chat-form').on('submit', function(event) {
            event.preventDefault();
            var message = $('#message-input').val();
            if (message.trim().length == 0) {
                $('#message-input').focus();
            } 
            else {
                var data = {
                    user_id: user_id,
                    other_user_id: other_user_id,
                    message: message,
                    otherUserName: otherUserName,
                }
                console.log('!! data');
                console.log(data);
                socket.emit('send_message', data);
                $('#message-input').val('');
            }
        });



        socket.on('users_connected', function() {            
            users_connected();
        });
        socket.on('user_disconnected', function(data) {
            users_connected();
        });
        socket.on('receive_message', function(data) {
            console.log('!! receive message '+data.user_id);
            count_messages(data.user_id);
        });
    });
</script>
@endpush