<?php

namespace App\Http\Controllers\Backend;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Jenssegers\Agent\Agent;


class MessagesController extends Controller
{
    public function index(Request $request)
    {
        // Chat functionality disabled due to missing package
        return view('backend.messages.index-desktop', [
            'threads' => [],
            'teachers' => [],
            'thread' => ""
        ]);
    }

    public function send(Request $request)
    {
        return redirect()->back()->withFlashDanger('Chat function is currently disabled.');
    }

    public function reply(Request $request)
    {
        return redirect()->back()->withFlashDanger('Chat function is currently disabled.');
    }

    public function getUnreadMessages(Request $request)
    {
        return ['unreadMessageCount' => 0, 'threads' => []];
    }
}

