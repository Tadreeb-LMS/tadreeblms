<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('assignment_pages.user_details.title') }}</title>
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
</head>

<body>
    <div class="my-5">
        @if(Session::has('message'))
        <div class="col-md-6 m-auto">
            <p class="alert {{ Session::get('alert-class', 'alert-info') }} text-center">{{ Session::get('message') }}</p>
        </div>
        @endif
        <form class="p-5 w-50 m-auto bg-primary" method="POST" action="{{route('online_assessment.store_user_details')}}">
            @csrf
            @if($urlVal)
            <input type="hidden" name="url_code" value="{{$urlVal->url_code}}">
            <input type="hidden" name="verify_code" value="{{$urlVal->verify_code}}">
            <input type="hidden" name="assignment_id" value="{{$urlVal->id}}">
            @endif
            <div class="form-group">
                <label for="first_name">{{ __('assignment_pages.user_details.first_name') }}</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="{{ __('assignment_pages.user_details.first_name_placeholder') }}" value="{{request()->get('first_name')}}">
            </div>
            <div class="form-group">
                <label for="last_name">{{ __('assignment_pages.user_details.last_name') }}</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="{{ __('assignment_pages.user_details.last_name_placeholder') }}" value="{{request()->get('last_name')}}">
            </div>
            <div class="form-group">
                <label for="email">{{ __('assignment_pages.user_details.email') }}</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="{{ __('assignment_pages.user_details.email_placeholder') }}" value="{{request()->get('email')}}">
            </div>
            <div class="form-group">
                <label for="phone">{{ __('assignment_pages.user_details.phone') }}</label>
                <input type="text" class="form-control" id="phone" maxlength="15" name="phone" required placeholder="{{ __('assignment_pages.user_details.phone_placeholder') }}" value="{{request()->get('phone')}}">
            </div>
            <div class="form-group">
                <button class="btn btn-info" type="submit">{{ __('assignment_pages.user_details.submit') }}</button>
            </div>
        </form>
    </div>

    <script type="text/javascript" src="{{asset('assets/js/popper.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/bootstrap.min.js')}}"></script>
</body>

</html>