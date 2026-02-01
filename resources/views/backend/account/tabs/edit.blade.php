{{ html()->modelForm($user, 'PATCH', route('admin.profile.update'))
    ->class('form-horizontal')
    ->attribute('enctype', 'multipart/form-data')
    ->open() }}

<div class="row">
    <div class="col">
        {{-- Avatar --}}
        <div class="form-group">
            {{ html()->label(__('validation.attributes.frontend.avatar'))->for('avatar') }}
            <div>
                <input type="radio" name="avatar_type" value="gravatar"
                    {{ $user->avatar_type == 'gravatar' ? 'checked' : '' }}>
                {{ __('validation.attributes.frontend.gravatar') }}

                &nbsp;&nbsp;

                <input type="radio" name="avatar_type" value="storage"
                    {{ $user->avatar_type == 'storage' ? 'checked' : '' }}>
                {{ __('validation.attributes.frontend.upload') }}

                @foreach($user->providers as $provider)
                    @if(strlen($provider->avatar))
                        <input type="radio" name="avatar_type"
                            value="{{ $provider->provider }}"
                            {{ $logged_in_user->avatar_type == $provider->provider ? 'checked' : '' }}>
                        {{ ucfirst($provider->provider) }}
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Preferred Language --}}
        <div class="form-group">
            {{ html()->label(__('Preferred Language'))->for('fav_lang') }}
            <div>
                <input type="radio" name="fav_lang" value="english"
                    {{ $user->fav_lang == 'english' ? 'checked' : '' }}> English
                &nbsp;&nbsp;
                <input type="radio" name="fav_lang" value="arabic"
                    {{ $user->fav_lang == 'arabic' ? 'checked' : '' }}> Arabic
            </div>
        </div>

        {{-- Avatar Upload --}}
        <div class="form-group" id="avatar_location" style="display:none">
            {{ html()->file('avatar_location')->class('form-control') }}
        </div>
    </div>
</div>

{{-- First Name --}}
<div class="row">
    <div class="col">
        <div class="form-group">
            {{ html()->label(__('validation.attributes.frontend.first_name'))->for('first_name') }}
            {{ html()->text('first_name')->class('form-control')->required() }}
        </div>
    </div>
</div>

{{-- Last Name --}}
<div class="row">
    <div class="col">
        <div class="form-group">
            {{ html()->label(__('validation.attributes.frontend.last_name'))->for('last_name') }}
            {{ html()->text('last_name')->class('form-control')->required() }}
        </div>
    </div>
</div>

@if($logged_in_user->hasRole('teacher'))
@php
    $teacherProfile = optional($logged_in_user->teacherProfile);
    $payment_details = optional(json_decode($teacherProfile->payment_details));
@endphp

{{-- Gender --}}
<div class="row">
    <div class="col">
        <div class="form-group">
            {{ html()->label(__('Gender'))->for('gender') }}
            <div>
                @foreach(['male','female','other'] as $gender)
                    <label class="mr-3">
                        <input type="radio" name="gender" value="{{ $gender }}"
                            {{ $logged_in_user->gender == $gender ? 'checked' : '' }}>
                        {{ ucfirst($gender) }}
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Social Links --}}
@foreach(['facebook','twitter','linkedin'] as $social)
<div class="row">
    <div class="col">
        <div class="form-group">
            {{ html()->label(__('labels.teacher.'.$social.'_link'))->for($social.'_link') }}
            {{ html()->text($social.'_link')->class('form-control')
                ->value($teacherProfile->{$social.'_link'}) }}
        </div>
    </div>
</div>
@endforeach

{{-- Payment Method --}}
<div class="row">
    <div class="col">
        <div class="form-group">
            {{ html()->label(__('labels.teacher.payment_details')) }}
            <select class="form-control" name="payment_method" id="payment_method" required>
                <option value="bank" {{ $teacherProfile->payment_method == 'bank'?'selected':'' }}>Bank</option>
                <option value="paypal" {{ $teacherProfile->payment_method == 'paypal'?'selected':'' }}>Paypal</option>
            </select>
        </div>
    </div>
</div>

<div class="bank_details" style="display:{{ $teacherProfile->payment_method == 'bank'?'block':'none' }}">
    <div class="form-group">
        {{ html()->label('Bank Name') }}
        {{ html()->text('bank_name')->class('form-control')->value($payment_details->bank_name) }}
    </div>
</div>

<div class="paypal_details" style="display:{{ $teacherProfile->payment_method == 'paypal'?'block':'none' }}">
    <div class="form-group">
        {{ html()->label('Paypal Email') }}
        {{ html()->email('paypal_email')->class('form-control')->value($payment_details->paypal_email) }}
    </div>
</div>
@endif

<div class="text-right">
    {{ form_submit(__('labels.general.buttons.update')) }}
</div>

{{ html()->closeModelForm() }}

@push('after-scripts')
<script>
$(function () {
    function toggleAvatar() {
        $('#avatar_location').toggle(
            $('input[name=avatar_type]:checked').val() === 'storage'
        );
    }
    toggleAvatar();
    $('input[name=avatar_type]').change(toggleAvatar);

    $('#payment_method').change(function () {
        $('.bank_details').toggle(this.value === 'bank');
        $('.paypal_details').toggle(this.value === 'paypal');
    });
});
</script>
@endpush
