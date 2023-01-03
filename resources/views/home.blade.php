@extends('layouts.app')

@section('content')
<div class="container">
    <div id="snippetContent">
        <main class="content">
            <div class="container p-0">
                <div class="card">
                    <div class="row g-0">
                        <div class="col-12 col-lg-5 col-xl-3">
                            <div class="row mx-2 py-2">
                                @foreach ($friends as $friend)
                                <a href="{{ route('home', ['id' => $friend->id]) }}" class="col-6 list-group-item list-group-item-action border-0">
                                    @if ($friend->unread_messages != 0)
                                    <div class="badge bg-success float-right" style="color: white">
                                        {{ $friend->unread_messages }}
                                    </div>
                                    @endif
                                    <div class="d-flex align-items-start">
                                        <img src="https://ui-avatars.com/api/?name={{ $friend->name }}" class="rounded-circle mr-1" alt="{{ $friend->name }}" width="40" height="40" />
                                        <div class="flex-grow-1 ml-3">
                                            {{ $friend->name }}
                                            <div class="small">
                                                @if ($friend->is_online == true)
                                                    <span class="fa fa-circle chat-online"></span> Online
                                                @else
                                                    <span class="fa fa-circle chat-offline"></span> Offline
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                            <hr class="d-block d-lg-none mt-1 mb-0" />
                        </div>
                        <div class="col-12 col-lg-7 col-xl-9">
                            @if(!empty($otherUser))
                            <div class="py-2 px-4 border-bottom d-none d-lg-block">
                                <div class="d-flex align-items-center py-1">
                                    <div class="position-relative">
                                        <img src="https://ui-avatars.com/api/?name={{ $otherUser->name }}" class="rounded-circle mr-1" alt="{{ $otherUser->name }}" width="40" height="40" />
                                    </div>
                                    <div class="flex-grow-1 pl-3">
                                        <strong>{{ $otherUser->name }}</strong>
                                        <div class="text-muted small"><em>Typing...</em></div>
                                    </div>
                                </div>
                            </div>
                            <div class="position-relative">
                                <div class="chat-messages p-4">
                                    @foreach ($messages as $message)
                                        @if ($message->user_id == Auth::id())
                                            <div class="chat-message-right pb-4">
                                                <div>
                                                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}" class="rounded-circle mr-1" alt="{{ Auth::user()->name }}" width="40" height="40" />
                                                    <div class="text-muted small text-nowrap mt-2">
                                                        {{ date('H:i', $message->created_at) }}
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                                                    <div class="font-weight-bold mb-1">You</div>
                                                    {!! $message->message !!}
                                                </div>
                                            </div>
                                        @else
                                            <div class="chat-message-left pb-4">
                                                <div>
                                                    <img src="https://ui-avatars.com/api/?name={{ $otherUser->name }}" class="rounded-circle mr-1" alt="{{ $otherUser->name }}" width="40" height="40" />
                                                    <div class="text-muted small text-nowrap mt-2">
                                                        {{ date('H:i', $message->created_at) }}
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                                                    <div class="font-weight-bold mb-1">{{ $otherUser->name }}</div>
                                                    {!! $message->message !!}
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex-grow-0 py-3 px-4 border-top">
                                <form id="chat-form">
                                    <div class="input-group">
                                        <input type="text" id="message-input" class="form-control" placeholder="Type your message" />
                                        <button type="submit" class="btn btn-primary">Envoyé</button>
                                    </div>
                                </form>
                            </div>
                            @endif
                        </div>
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
        
        $('.chat-messages').animate({scrollTop: $('.chat-messages').prop('scrollHeight')}, 'fast');
        $('.chat-messages').attr('scroll-top', 100);
        
    }

    init();


    $(function() {

        var user_id = "{{ Auth::id() }}";
        var other_user_id = '{{ ($otherUser) ? $otherUser->id:'' }}';
        var otherUserName = '{{ ($otherUser) ? $otherUser->name:'' }}';
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



        socket.on('user_connected', function(data) {
            $('#status_' + data).html('<span class="fa fa-circle chat-online"></span> Online');
        });
        socket.on('user_disconnected', function(data) {
            $('#status_' + data).html('<span class="fa fa-circle chat-offline"></span> Offline');
        });
        socket.on('receive_message', function(data) {
            if ((parseInt(data.user_id) == user_id && parseInt(data.other_user_id) == other_user_id) || (parseInt(data.user_id) == other_user_id && parseInt(data.other_user_id) == user_id)) {

                var h = new Date(data.time * 1000);
                var min = h.getHours() + ':' + h.getMinutes();

                if ((parseInt(data.user_id) == user_id)) {
                    var html = '<div class="chat-message-right pb-4">';
                    html += '<div>';
                    html += '<img src="https://ui-avatars.com/api/?name=' + data.otherUserName + '" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40" />';
                    html += '<div class="text-muted small text-nowrap mt-2">' + min + '</div>';
                    html += '</div>';
                    html += '<div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">';
                    html += '<div class="font-weight-bold mb-1">You</div>';
                    html += data.message;
                    html += '</div>';
                    html += '</div>';
                } 
                else {
                    var html = '<div class="chat-message-left pb-4">';
                    html += '<div>';
                    html += '<img src="https://ui-avatars.com/api/?name=' + data.otherUserName + '" class="rounded-circle mr-1" alt="Chris Wood" width="40" height="40" />';
                    html += '<div class="text-muted small text-nowrap mt-2">' + min + '</div>';
                    html += '</div>';
                    html += '<div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">';
                    html += '<div class="font-weight-bold mb-1">' + data.otherUserName + '</div>';
                    html += data.message;
                    html += '</div>';
                    html += '</div>';
                }
                console.log('!! html');
                console.log(html);
                $('.chat-messages').append(html);
                $('.chat-messages').animate({
                    scrollTop: $('.chat-messages').prop('scrollHeight')
                }, 1000);
                socket.emit('read_message', data.id);
            }
        });
    });
</script>
@endpush