@extends('backend.layouts.app')
@section('title', __('admin_pages.manual_assessment.user_assessment_answers') . ' | ' . app_name())
 <style>
    .card-header.bg-primary.text-white {
    color: #000 !important;
}
</style>
@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 card mb-3 p-4">
                <h4>{{ __('admin_pages.manual_assessment.marks_obtained') }}: {{ $marks }}</h4>
                @php
                    $i = 1;
                @endphp
                @foreach ($assessements as $assessement)
                    <div class="">
                        <h3 class="m-3">{{ $assessement->title }}</h3>

                        @foreach ($assessement->test_active_questions($is_completed, $completed_at) as $question)
                            @php
                                $user_answer = @$question->getUserAnswer(
                                    request()->user_id,
                                );

                                //dd($user_answer);

                                if(empty($question->solution)) {
                                    $question->solution = $question->getCorrectAnswer($question->id);
                                }
                            @endphp
                            <div class="card-header bg-primary text-white" data-id="{{ $question->id }}"
                                data-ans="{{ @$user_answer['is_correct'] }}">
                                {{ $i++ }}. {!! $question->question_text !!}
                                
                                </br><span> {{ __('course_pages.admin_test_questions_index.marks') }}: {{ $question->marks }}</span>
                            </div>
                            <div class="card-body">
                                {{-- <h5 class="card-title">What is the capital of France?</h5> --}}
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>{{ __('admin_pages.manual_assessment.correct_answer') }}:</br></strong> {!! $question->solution !!}
                                    </li>
                                    <li class="list-group-item">
                                        <strong>{{ __('admin_pages.manual_assessment.user_answer') }}:</br></strong>
                                         {!! $user_answer['answer'] ?? __('admin_pages.manual_assessment.not_available') !!}
                                        @if ($user_answer)
                                            <span @class([
                                                'badge',
                                                'bg-success' => $user_answer['is_correct'],
                                                'bg-danger' => !$user_answer['is_correct'],
                                            ])><i @class([
                                                'fas',
                                                'fa-check' => $user_answer['is_correct'],
                                                'fa-times' => !$user_answer['is_correct'],
                                            ])></i>
                                            </span>
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endforeach

            </div>
        </div>
    </div>
@endsection
