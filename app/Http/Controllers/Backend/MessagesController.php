<?php

namespace App\Http\Controllers\Backend;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Jenssegers\Agent\Agent;
use Lexx\ChatMessenger\Models\Message;
use Lexx\ChatMessenger\Models\Participant;
use Lexx\ChatMessenger\Models\Thread;
use Messenger;

class MessagesController extends Controller
{
    public function index(Request $request)
    {
        $thread="";


        $teachers = User::role('teacher')->get()
            ->where('id', '!=', auth()->user()->id)
            ->pluck('name', 'id');


        auth()->user()->load('threads.messages.user');


        $threads = auth()->user()->threads;

        if (request()->has('thread') && ($request->thread != null)) {
            if (request('thread')) {
                $thread = auth()->user()->threads()
                   ->where('chat_threads.id', '=', $request->thread)
                   ->first();

                //Read Thread
                $thread->markAsRead(auth()->user()->id);
            } elseif ($thread == "") {
                abort(404);
            }
        }

        $agent = new Agent();

        if ($agent->isMobile()) {
            $view = 'backend.messages.index-mobile';
        } else {
            $view = 'backend.messages.index-desktop';
        }
        return view($view, [
            'threads' => $threads,
            'teachers' => $teachers,
            'thread' => $thread
        ]);
    }

    public function send(Request $request)
    {
        $this->validate($request, [
           'recipients' => 'required',
           'message' => 'required'
        ], [
           'recipients.required' => 'Please select at least one recipient',
           'message.required' => 'Please input your message'
        ]);

        $thread = Thread::create();

        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => auth()->user()->id,
            'body' => $request->message,
        ]);

        $participant = Participant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => auth()->user()->id,
        ]);
        $participant->last_read = Carbon::now();
        $participant->save();

        if ($request->has('recipients')) {
            $thread->addParticipant($request ->recipients);
        }


        return redirect(route('admin.messages').'?thread='.$thread->id);
    }

    public function reply(Request $request)
    {
        $this->validate($request, [
            'message' => 'required'
        ], [
            'message.required' => 'Please input your message'
        ]);

        $message = Message::create([
            'thread_id' => $request->thread_id,
            'user_id' => auth()->user()->id,
            'body' => $request->message,
        ]);

        $participant = Participant::firstOrCreate([
            'thread_id' => $request->thread_id,
            'user_id' => auth()->user()->id,
        ]);
        $participant->last_read = Carbon::now();
        $participant->save();

        return redirect(route('admin.messages').'?thread='.$message->thread_id)->withFlashSuccess('Message sent successfully');
    }

    public function getUnreadMessages(Request $request)
    {
        if (!method_exists(auth()->user(), 'unreadMessagesCount')) {
            return ['unreadMessageCount' => 0, 'threads' => []];
        }
        $unreadMessageCount = auth()->user()->unreadMessagesCount();
        $unreadThreads = [];
        foreach (auth()->user()->threads as $item) {
            if ($item->userUnreadMessagesCount(auth()->user()->id)) {
                $data = [
                  'thread_id' => $item->id,
                  'message' => str_limit($item->messages()->orderBy('id', 'desc')->first()->body, 35),
                  'unreadMessagesCount' => $item->userUnreadMessagesCount(auth()->user()->id),
                  'title' => $item->participants()->with('user')->where('user_id', '<>', auth()->user()->id)->first()->user->name
                ];
                $unreadThreads[] = $data;
            }
        }
        return ['unreadMessageCount' =>$unreadMessageCount,'threads' => $unreadThreads];
    }
}
