@foreach ($users as $user)
    <div class="user col-lg-3">
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