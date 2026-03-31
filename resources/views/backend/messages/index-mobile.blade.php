@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@push('after-styles')
    @if(session()->get('display_type') && session()->get('display_type') == "rtl")
        <style>
            .message-box .msg_send_btn{
                right: unset !important;
                left: 0 !important;
            }
        </style>
    @endif
    <style>
        textarea {
            resize: none;
        }
    </style>
@endpush
@section('content')
    <div class="card message-box">
        <div class="card-header">
            <h3 class="page-title mb-0">@lang('labels.backend.messages.title')
                <div class="float-right">
                    <a class="text-decoration-none mr-2 refresh" href="#">
                        <i class="icon-refresh font-weight-bold"></i>
                    </a>
                    <a class="text-decoration-none compose mr-2" href="#">
                        <i class="icon-plus font-weight-bold"></i>
                    </a>
                    <a href="{{route('admin.messages')}}" class="d-lg-none text-decoration-none threads d-md-none">
                        <i class="icon-speech font-weight-bold"></i>
                    </a>
                </div>
            </h3>
        </div>
        <div class="card-body">
            <div class="messaging">
                <div class="inbox_msg">
                    <div class="inbox_people @if(request('thread')) d-none @endif d-md-block d-lg-block ">
                        <div class="headind_srch">
                            <div class="recent_heading btn-sm btn btn-dark">
                                <a class="text-decoration-none compose" href="#">
                                    <h5 class="text-white mb-0"><i class="icon-plus"></i>&nbsp;&nbsp; @lang('labels.backend.messages.compose')</h5>
                                </a>

                            </div>
                            <div class="srch_bar">
                                <div class="stylish-input-group">
                                    <input type="text" class="search-bar" id="myInput" placeholder="Search">
                                    <span class="input-group-addon">
                                        <button type="button">
                                            <i class="fa fa-search" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="inbox_chat">
                            @if(collect($threads)->count() > 0)
                                @foreach($threads as $item)
                                    @if($item->latestMessage)
                                        <a  href="{{route('admin.messages').'?thread='.$item->id}}" class="@if($item->userUnreadMessagesCount(auth()->user()->id)) unread
                                            @endif">
                                            <div data-thread="{{$item->id}}"
                                                 class="chat_list @if(!empty($thread) && ($thread->id == $item->id))  active_chat @endif ">
                                                <div class="chat_people">

                                                    <div class="chat_ib">
                                                        <h5>
                                                            {{ $item->participants()->with('user')->where('user_id','<>', auth()->user()->id)->first()->user->name }}
                                                            @if($item->participants()->count() > 2)
                                                                + {{ ($item->participants()->count()-2) }} @lang('labels.general.more')
                                                            @endif
                                                            <span
                                                                    class="chat_date">{{ $item->messages()->orderBy('id', 'desc')->first()->created_at->diffForHumans() }}</span>
                                                        </h5>
                                                        <p>{{ str_limit($item->messages()->orderBy('id', 'desc')->first()->body, 35) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>

                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @if(request()->has('thread') && !empty($thread))
                        <form method="post" class="compose-reply" action="{{route('admin.messages.reply')}}">
                            @csrf

                            <input type="hidden" name="thread_id" value="{{isset($thread->id) ? $thread->id : 0}}">
                            <div class="headind_srch ">
                                <div class="chat_people box-header">
                                    <div class="chat_img float-left">
                                        <img src="https://ptetutorials.com/images/user-profile.png"
                                             alt="" height="35px"></div>
                                    <div class="chat_ib float-left">

                                        <h5 class="mb-0 d-inline float-left">
                                            {{$thread->participants()->with('user')->where('user_id','<>', auth()->user()->id)->first()->user->name }}
                                            @if($thread->participants()->count() > 2)
                                                + {{ ($thread->participants()->count()-2) }} @lang('labels.general.more')
                                            @endif
                                        </h5>
                                        <p class="float-right d-inline mb-0">
                                            <a class="" href="{{route('admin.messages',['thread'=>$thread->id])}}">
                                                <i class="icon-refresh font-weight-bold"></i>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="mesgs">
                                <div class="msg_history">
                                    @if(count($thread->messages) > 0 )
                                        @foreach($thread->messages as $message)
                                            @if($message->user_id == auth()->user()->id)
                                                <div class="outgoing_msg">
                                                    <div class="sent_msg">
                                                        <p>{{$message->body}}</p>
                                                        <span class="time_date text-right"> {{\Carbon\Carbon::parse($message->created_at)->format('h:i A | M d Y')}}
                                                    </span></div>
                                                </div>
                                            @else
                                                <div class="incoming_msg">
                                                    <div class="incoming_msg_img"><img
                                                                src="https://ptetutorials.com/images/user-profile.png"
                                                                alt=""></div>
                                                    <div class="received_msg">
                                                        <div class="received_withd_msg">
                                                            <p>{{$message->body}}</p>
                                                            <span class="time_date">{{\Carbon\Carbon::parse($message->created_at)->format('h:i A | M d Y')}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                                <div class="type_msg">
                                    <div class="input_msg_write">
                                        <textarea type="text" name="message" class="write_msg"
                                                  placeholder="Type a message"></textarea>
                                        <button class="msg_send_btn" type="submit">
                                            <i class="icon-paper-plane" style="line-height: 2" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                    <form method="post" class="d-none compose-message" action="{{route('admin.messages.send')}}">
                        @csrf

                        <div class="headind_srch bg-dark">
                            <div class="chat_people header row">
                                <div class="col-12 col-lg-3">
                                    <p class="font-weight-bold text-white mb-0" style="line-height: 35px">@lang('labels.backend.messages.select_recipients'):</p>
                                </div>
                                <div class="col-lg-9 col-12 text-dark">
                                    {!! Form::select('recipients[]', $teachers, (request('teacher_id') ? request('teacher_id') : old('recipients')), ['class' => 'form-control select2', 'multiple' => 'multiple']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="mesgs">
                            <div class="msg_history">
                                <p class="text-center">{{trans('labels.backend.messages.start_conversation')}}</p>
                            </div>
                            <div class="type_msg">
                                <div class="input_msg_write">
                                    {{--<input type="text" class="write_msg" placeholder="Type a message"/>--}}
                                    <textarea type="text" name="message" class="write_msg"
                                              placeholder="{{trans('labels.backend.messages.type_a_message')}}"></textarea>
                                    <button class="msg_send_btn" type="submit">
                                        <i class="icon-paper-plane" style="line-height: 2" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
@push('after-scripts')
    <script>
        $(document).ready(function () {
            //Get to the last message in conversation
            $('.msg_history').animate({
                scrollTop: $('.msg_history')[0].scrollHeight
            }, 1000);

            //Read message
            setTimeout(function () {
                var thread = '{{request('thread')}}';
                $(".inbox_chat").find("[data-thread='" + thread + "']").removeClass('unread');
            }, 1000);

            //Get compose message screen
            $(document).on('click', '.compose', function (e) {
                e.preventDefault();
                $('.inbox_people').addClass('d-none');
                $('.compose-message').removeClass('d-none');
                $('.compose-reply').addClass('d-none')
            });

            //Refresh page to get updated conversation
            $(document).on('click', '.refresh', function (e) {
                e.preventDefault();
                location.reload();
            });

            //Filter in conversation
            $("#myInput").on("keyup", function () {
                var value = $(this).val().toLowerCase();
                $(".chat_list").parent('a').filter(function () {
                    $(this).toggle($(this).find('h5,p').text().toLowerCase().trim().indexOf(value) > -1)
                });
            });
        });

    </script>
@endpush
