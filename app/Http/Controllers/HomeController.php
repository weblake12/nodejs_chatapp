<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use App\User;
use App\Chat;
use App\Group;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request, $id = null) {
        $otherUser = null;
        $messages = [];
        $user_id = auth()->user()->id;
        if (!is_null($id)) {
            $otherUser = User::findOrfail($id);
            $group_id = (auth()->user()->id > $id) ? auth()->user()->id.$id:$id.auth()->user()->id;
            $messages = Chat::where('group_id', $group_id)->get();
            $chats = Chat::where('user_id', $id)->where('other_user_id', auth()->user()->id)->where('is_read', 0)->update(['is_read' => 1]);
        }
        else{
            return redirect()->route('users.connected');
        }
        $friends = User::where('deleted', 0)->where('id', '!=', auth()->user()->id)->select('*', DB::raw("(SELECT count(id) from chats where chats.other_user_id=$user_id and chats.user_id=users.id and chats.is_read = false) as unread_messages"))->get();
        return view('home', compact('friends', 'messages', 'otherUser'));
    }

    /**
     * Show the list of connected users.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function users_connected () {
        $title = 'Les utilisateurs connectés';
        $users = User::where('deleted', 0)->where('is_online', true)->where('id', '!=', auth()->user()->id)->select('*', DB::raw("(SELECT count(id) from chats where chats.other_user_id=".auth()->user()->id." and chats.user_id=users.id and chats.is_read = false) as unread_messages"))->get();
        return view('users', compact('users', 'title'));
    }

    /**
     * Show the list of users.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function users () {
        $users = User::where('deleted', 0)->where('id', '!=', auth()->user()->id)->get();
        return view('users', compact('users'));
    }

    /**
     * Show user.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function user (User $user) {
        return view('user', compact('user'));
    }













    public function ajax_users_connected (Request $request) {
        $users = User::where('deleted', 0)->where('is_online', true)->where('id', '!=', auth()->user()->id)->select('*', DB::raw("(SELECT count(id) from chats where chats.other_user_id=".auth()->user()->id." and chats.user_id=users.id and chats.is_read = false) as unread_messages"))->get();
        return view('partials.users', compact('users'));
    }

    public function ajax_count_messages (Request $request) {
        return response()->json(Chat::where('user_id', $request->user_id)->where('other_user_id', auth()->user()->id)->where('is_read', false)->count());
    }
}
