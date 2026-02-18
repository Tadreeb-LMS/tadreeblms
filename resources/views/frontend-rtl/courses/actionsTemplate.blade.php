@can($gateKey.'view')
    <a href="{{ route($routeKey.'.show', $row->id) }}"
       class="btn btn-xs btn-primary">@lang('global.app_view')</a>
@endcan
@can($gateKey.'edit')
    <a href="{{ route($routeKey.'.edit', $row->id) }}" class="btn btn-xs btn-info">@lang('global.app_edit')</a>
@endcan
@can($gateKey.'delete')
    <form style="display: inline-block;" method="POST" action="{{ route($routeKey.'.destroy', $row->id) }}" onsubmit="return confirm('{{ trans('global.app_are_you_sure') }}')?">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-xs btn-danger">{{ trans('global.app_delete') }}</button>
    </form>
@endcan