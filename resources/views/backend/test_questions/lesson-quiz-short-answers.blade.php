@extends('backend.layouts.app')

@section('title', 'Review Short Answers | ' . app_name())

@section('content')
    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h4>Review Short Answers</h4>
        <div>
            <a class="btn add-btn" href="{{ route('admin.test_questions.index') }}">Back to Questions</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <a class="btn btn-sm {{ request('status') !== 'reviewed' ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin.test_questions.lesson_quiz_short_answers') }}">Pending</a>
                <a class="btn btn-sm {{ request('status') === 'reviewed' ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin.test_questions.lesson_quiz_short_answers', ['status' => 'reviewed']) }}">Reviewed</a>
            </div>

            <div class="table-responsive">
                <h5>Lesson Quiz Short Answers</h5>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Lesson</th>
                            <th>Question</th>
                            <th>Expected Solution</th>
                            <th>Submitted Answer</th>
                            <th>Status</th>
                            <th style="width: 170px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($answers as $answer)
                            <tr>
                                <td>
                                    {{ trim(($answer->first_name ?? '') . ' ' . ($answer->last_name ?? '')) ?: $answer->email }}
                                    @if($answer->email)
                                        <br><small>{{ $answer->email }}</small>
                                    @endif
                                </td>
                                <td>{{ $answer->course_title ?? '-' }}</td>
                                <td>{{ $answer->lesson_title ?? '-' }}</td>
                                <td>{!! $answer->question_text !!}</td>
                                <td>{!! $answer->solution ?: '-' !!}</td>
                                <td>{{ $answer->answer_text }}</td>
                                <td>
                                    @if(is_null($answer->is_correct))
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif((int) $answer->is_correct === 1)
                                        <span class="badge badge-success">Approved</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.test_questions.lesson_quiz_short_answers.review', $answer->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="is_correct" value="1">
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.test_questions.lesson_quiz_short_answers.review', $answer->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="is_correct" value="0">
                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No short answers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $answers->links() }}

            <div class="table-responsive mt-4">
                <h5>Final Assessment Short Answers</h5>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Question</th>
                            <th>Expected Solution</th>
                            <th>Submitted Answer</th>
                            <th>Status</th>
                            <th style="width: 170px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessmentAnswers as $answer)
                            <tr>
                                <td>
                                    {{ trim(($answer->first_name ?? '') . ' ' . ($answer->last_name ?? '')) ?: $answer->email }}
                                    @if($answer->email)
                                        <br><small>{{ $answer->email }}</small>
                                    @endif
                                </td>
                                <td>{{ $answer->course_title ?? '-' }}</td>
                                <td>{!! $answer->question_text !!}</td>
                                <td>{!! $answer->solution ?: '-' !!}</td>
                                <td>{{ $answer->answer_text ?? $answer->answer }}</td>
                                <td>
                                    @if((int) $answer->is_correct === 0)
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif((int) $answer->is_correct === 1)
                                        <span class="badge badge-success">Approved</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.test_questions.assessment_short_answers.review', $answer->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="is_correct" value="1">
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.test_questions.assessment_short_answers.review', $answer->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="is_correct" value="2">
                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No final assessment short answers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $assessmentAnswers->links() }}
        </div>
    </div>
@endsection
