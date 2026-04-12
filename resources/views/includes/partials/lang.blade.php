<div class="dropdown-menu dropdown-menu-right add-dropmenu-position" aria-labelledby="navbarDropdownLanguageLink">
    {{--@foreach(array_keys(config('locale.languages')) as $lang)--}}
        {{--@if($lang != app()->getLocale())--}}
            {{--<small><a href="{{ route('locale.swap', ['lang' => $lang]) }}" class="dropdown-item">{{ trans('menus.language_picker.langs.' . $lang, [], 'en') }}</a></small>--}}
        {{--@endif--}}
    {{--@endforeach--}}
    @foreach($locales as $lang)
        @if($lang != app()->getLocale())
            <small><a href="{{ route('locale.swap', ['lang' => $lang]) }}" class="dropdown-item">{{ locale_label($lang) }}</a></small>
        @endif
    @endforeach
</div>
