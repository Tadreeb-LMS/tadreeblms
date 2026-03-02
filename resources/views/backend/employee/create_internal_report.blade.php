@extends('backend.layouts.app')

@section('title', 'Internal Report'.' | '.app_name())

@section('content')
    {{ html()->form('POST', route('admin.employee.reports_store_internal'))->acceptsFiles()->class('form-horizontal')->open() }}
    <div class="card">
        
        <div class="card-header">
            <h3 class="page-title d-inline">Create Internal Report</h3>
            <div class="float-right">
                <a href="{{ route('admin.employee.internal_reports') }}"
                   class="btn btn-success">View Report</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">

                    <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="test_id">Users</label>
                        <div class="col-md-10">
                        {!! Form::select('teachers', $teachers, old('teachers'), ['class' => 'form-control select2 js-example-placeholder-multiple' ,'required' => false]) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-2 form-control-label" for="first_name">@lang('Select Department')</label>
                        <div class="col-md-10">
                            <select name="department" class="form-control">
                                <option value=""> Select One </option>
                                @foreach($departments as $row)
                                    <option value="{{ $row->id }}"> {{ $row->title }} </option>
                                @endforeach
                            </select>
                        </div><!--col-->
                    </div>

                    <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="verify_code">Score</label>
                    <div class="col-md-10">
                        <input class="form-control" type="text" name="score" id="score" placeholder="Enter score" required="" autofocus="">
                    </div>
                   </div>

                   <div class="form-group row">
                    <label class="col-md-2 form-control-label" for="first_name">Exam status</label>
                    <div class="col-md-10">
                    <select name="status" class="form-control">
                                <option value="">Exam Status</option>
                                    <option value="Pass"> Pass </option>
                                    <option value="Fail"> Fail</option>
                            </select>
                    </div>
                    </div>

                    <div class="form-group row justify-content-center">
                        <div class="col-4">
                            {{ form_cancel(route('admin.employee.index'), __('buttons.general.cancel')) }}
                            {{ form_submit(__('buttons.general.crud.create')) }}
                        </div>
                    </div><!--col-->
                </div>
            </div>
        </div>
    </div>
    {{ html()->form()->close() }}
@endsection
@push('after-scripts')
<script>
    @if(old('payment_method') && old('payment_method') == 'bank')
    $('.paypal_details').hide();
    $('.bank_details').show();
    @elseif(old('payment_method') && old('payment_method') == 'paypal')
    $('.paypal_details').show();
    $('.bank_details').hide();
    @else
    $('.paypal_details').hide();
    @endif
    $(document).on('change', '#payment_method', function(){
        if($(this).val() === 'bank'){
            $('.paypal_details').hide();
            $('.bank_details').show();
        }else{
            $('.paypal_details').show();
            $('.bank_details').hide();
        }
    });
</script>
@endpush
