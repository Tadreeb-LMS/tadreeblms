@extends('backend.layouts.app')
@section('title', __('labels.backend.questions.title').' | '.app_name())

@section('content')

    {!! Form::model($question, ['method' => 'PUT', 'route' => ['admin.questions.update', $question->id], 'files' => true,]) !!}

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.questions.edit')</h3>
            <div class="float-right">
                <a href="{{ route('admin.questions.index') }}"
                   class="btn btn-success">@lang('labels.backend.questions.view')</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('question',  trans('labels.backend.questions.fields.question').'*', ['class' => 'control-label']) !!}
                    {!! Form::textarea('question', old('question'), ['class' => 'form-control ', 'placeholder' => '', 'required' => '']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('question'))
                        <p class="help-block">
                            {{ $errors->first('question') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="row">
                @if ($question->question_image)
                    <div class="col-9">
                        {!! Form::label('question_image', trans('labels.backend.questions.fields.question_image'), ['class' => 'control-label']) !!}
                        {!! Form::file('question_image', ['class' => 'form-control', 'style' => 'margin-top: 4px;']) !!}
                        {!! Form::hidden('question_image_max_size', 8) !!}
                        {!! Form::hidden('question_image_max_width', 4000) !!}
                        {!! Form::hidden('question_image_max_height', 4000) !!}
                        <p class="help-block"></p>
                        @if($errors->has('question_image'))
                            <p class="help-block">
                                {{ $errors->first('question_image') }}
                            </p>
                        @endif
                    </div>

                    <div class="col-1 form-group">
                        <a href="{{ asset('storage/uploads/'.$question->question_image) }}" target="_blank">
                            <img height="70px" src="{{ asset('storage/uploads/'.$question->question_image) }}"></a>
                    </div>
                @else
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('question_image', trans('labels.backend.questions.fields.question_image'), ['class' => 'control-label']) !!}
                    {!! Form::file('question_image', ['class' => 'form-control', 'style' => 'margin-top: 4px;']) !!}
                    {!! Form::hidden('question_image_max_size', 8) !!}
                    {!! Form::hidden('question_image_max_width', 4000) !!}
                    {!! Form::hidden('question_image_max_height', 4000) !!}
                    <p class="help-block"></p>
                    @if($errors->has('question_image'))
                        <p class="help-block">
                            {{ $errors->first('question_image') }}
                        </p>
                    @endif
                </div>
                @endif

            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('score', trans('labels.backend.questions.fields.score').'*', ['class' => 'control-label']) !!}
                    {!! Form::number('score', old('score'), ['class' => 'form-control', 'placeholder' => '', 'required' => '', 'min' => 1, 'max' => 100]) !!}
                    <p class="help-block"></p>
                    @if($errors->has('score'))
                        <p class="help-block">
                            {{ $errors->first('score') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('tests', trans('labels.backend.questions.fields.tests'), ['class' => 'control-label']) !!}
                    {!! Form::select('tests[]', $tests, old('tests') ? old('tests') : $question->tests->pluck('id')->toArray(), ['class' => 'form-control select2', 'multiple' => 'multiple', 'required' => true]) !!}
                    <p class="help-block"></p>
                    @if($errors->has('tests'))
                        <p class="help-block">
                            {{ $errors->first('tests') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if($question->options->count())
    {!! Form::hidden('options_available', 1) !!}
    @foreach ($question->options as $key=>$option)
        @php $key++ @endphp
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('option_text_' . $option->id, trans('labels.backend.questions.fields.option_text').'*', ['class' => 'control-label']) !!}
                        {!! Form::textarea('option_text_' . $key, $option->option_text, ['class' => 'form-control ', 'rows' => 3, 'required' => true]) !!}
                        <p class="help-block"></p>
                        @if($errors->has('option_text_' . $option->id))
                            <p class="help-block">
                                {{ $errors->first('option_text_' . $option->id) }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('explanation_' . $option->id, trans('labels.backend.questions.fields.option_explanation').'*', ['class' => 'control-label']) !!}
                        {!! Form::textarea('explanation_' . $key, $option->explanation, ['class' => 'form-control ', 'rows' => 3]) !!}
                        <p class="help-block"></p>
                        @if($errors->has('explanation_' . $option->id))
                            <p class="help-block">
                                {{ $errors->first('explanation_' . $option->id) }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('correct_' . $key, trans('labels.backend.questions.fields.correct'), ['class' => 'control-label']) !!}
                        {!! Form::hidden('correct_' . $option->id, 0) !!}
                        {!! Form::hidden('option_id_'.$key,  $option->id ) !!}
                        {!! Form::checkbox('correct_' . $key, 1, ($option->correct == 1) ? true : false, []) !!}
                        <p class="help-block"></p>
                        @if($errors->has('correct_' . $question))
                            <p class="help-block">
                                {{ $errors->first('correct_' . $question) }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @else
    {!! Form::hidden('options_available', 0) !!}
    @for ($question=1; $question<=4; $question++)
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('option_text_' . $question, trans('labels.backend.questions.fields.option_text').'*', ['class' => 'control-label']) !!}
                        {!! Form::textarea('option_text_' . $question, old('option_text'), ['class' => 'form-control ', 'rows' => 3, 'required' =>  true]) !!}
                        <p class="help-block"></p>
                        @if($errors->has('option_text_' . $question))
                            <p class="help-block">
                                {{ $errors->first('option_text_' . $question) }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('explanation_' . $question, trans('labels.backend.questions.fields.option_explanation'), ['class' => 'control-label']) !!}
                        {!! Form::textarea('explanation_' . $question, old('explanation_'.$question), ['class' => 'form-control ', 'rows' => 3]) !!}
                        <p class="help-block"></p>
                        @if($errors->has('explanation_' . $question))
                            <p class="help-block">
                                {{ $errors->first('explanation_' . $question) }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('correct_' . $question, trans('labels.backend.questions.fields.correct'), ['class' => 'control-label']) !!}
                        {!! Form::hidden('correct_' . $question, 0) !!}
                        {!! Form::checkbox('correct_' . $question, 1, false, []) !!}
                        <p class="help-block"></p>
                        @if($errors->has('correct_' . $question))
                            <p class="help-block">
                                {{ $errors->first('correct_' . $question) }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endfor
    @endif
    <div class="row">
        <div class="col-12 text-center mb-4">
            {!! Form::submit(trans('strings.backend.general.app_update'), ['class' => 'btn btn-danger']) !!}

        </div>
    </div>


    {!! Form::close() !!}
@stop

