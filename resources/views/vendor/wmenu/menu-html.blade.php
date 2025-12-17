<?php
$currentUrl = url()->current();
if (config('nav_menu') != 0) {
    $nav_menu = \Harimayco\Menu\Models\Menus::findOrFail(config('nav_menu'));
}
?>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<link href="{{ asset('vendor/harimayco-menu/style.css') }}" rel="stylesheet">

<div id="hwpwrap">
    <div class="custom-wp-admin wp-admin wp-core-ui js menu-max-depth-0 nav-menus-php auto-fold admin-bar">
        <div id="wpwrap">
            <div id="wpcontent">
                <div id="wpbody">
                    <div id="wpbody-content">
                        <div class="wrap">

                            {{-- MENU SELECT --}}
                            <div class="manage-menus">
                                <form method="get" action="{{ $currentUrl }}">
                                    <label class="selected-menu">
                                        {{ __('strings.backend.menu_manager.select_to_edit') }}
                                    </label>

                                    {!! Menu::select('menu', $menulist) !!}

                                    <span class="submit-btn">
                                        <input type="submit" class="button-secondary" value="Choose">
                                    </span>

                                    <span class="add-new-menu-action">
                                        or <a href="{{ $currentUrl }}?action=edit&menu=0">
                                            {{ __('strings.backend.menu_manager.create_new') }}
                                        </a>.
                                    </span>
                                </form>
                            </div>

                            <div id="nav-menus-frame" class="row">

                                {{-- LEFT COLUMN --}}
                                @if(request()->has('menu') && request('menu'))
                                <div class="col-3">

                                    {{-- PAGES --}}
                                    @if(isset($pages))
                                    <div class="accordion-container mt-4">
                                        <ul class="outer-border">
                                            <li class="control-section accordion-section open">
                                                <h3 class="accordion-section-title hndle">
                                                    {{ __('strings.backend.menu_manager.pages') }}
                                                </h3>

                                                <div class="card-body">
                                                    <input type="text" class="form-control searchInput mb-3"
                                                           placeholder="Search Pages">

                                                    <div class="checkbox-wrapper page">
                                                        @foreach($pages as $item)
                                                        <div class="checkbox" data-value="{{ $item->title }}">
                                                            <label>
                                                                <input type="checkbox" value="{{ $item->id }}">
                                                                {{ $item->title }}
                                                            </label>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div class="action-wrapper border-top pt-2 pb-2">
                                                    <label class="my-2">
                                                        <input type="checkbox" class="select_all">
                                                        {{ __('strings.backend.menu_manager.select_all') }}
                                                    </label>

                                                    <button class="btn btn-light add-to-menu float-right">
                                                        {{ __('strings.backend.menu_manager.add_to_menu') }}
                                                    </button>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                                @endif

                                {{-- RIGHT COLUMN --}}
                                <div class="col-lg-9 col-12">

                                    <input type="hidden" id="idmenu" value="{{ request('menu') }}">

                                    <ul class="menu ui-sortable" id="menu-to-edit">
                                        @foreach($menus ?? [] as $m)
                                        <li class="menu-item" id="menu-item-{{ $m->id }}">

                                            <div class="menu-item-settings">

                                                <input type="hidden" name="menuid_{{ $m->id }}" value="{{ $m->id }}">

                                                <label>
                                                                    {{ __('strings.backend.menu_manager.label') }} (EN)
                                                                    <input type="text"
                                                                        id="idlabelmenu_{{ $m->id }}"
                                                                        value="{{ $m->label }}"
                                                                        class="form-control">
                                                                </label>

                                                                {{-- <label class="mt-2">
                                                                    {{ __('strings.backend.menu_manager.label') }} (AR)
                                                                    <input type="text"
                                                                        id="idlabelmenu_ar_{{ $m->id }}"
                                                                        value="{{ $m->label_ar ?? '' }}"
                                                                        class="form-control"
                                                                        dir="rtl">
                                                                </label> --}}

                                                <label class="mt-2">
                                                    {{ __('strings.backend.menu_manager.url') }}
                                                    <input type="text"
                                                           id="url_menu_{{ $m->id }}"
                                                           name="url_menu_{{ $m->id }}"
                                                           value="{{ $m->link }}"
                                                           class="form-control">
                                                </label>

                                                <div class="text-right mt-2">
                                                    <a onclick="updateitem({{ $m->id }})"
                                                       href="javascript:void(0)"
                                                       class="btn btn-primary">
                                                        {{ __('strings.backend.menu_manager.update_item') }}
                                                    </a>
                                                </div>

                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('after-scripts')
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
});

// UPDATE MENU ITEM (FIXED)
function updateitem(id) {
    let label_en = document.getElementById('idlabelmenu_' + id).value;
    let label_ar = document.getElementById('idlabelmenu_ar_' + id).value;
    let url      = document.getElementById('url_menu_' + id).value;
    let menu     = document.getElementById('idmenu').value;

    

    $.post("{{ route('hupdateitem') }}", {
        id: id,
        label: label_en,
        label_ar: label_ar,
        url: url,
        idmenu: menu
    })
    .done(() => location.reload())
    .fail(err => {
        console.error(err.responseText);
        alert('Update failed');
    });
}


// ADD TO MENU
$(document).on('click', '.add-to-menu', function () {
    let data = [];
    let card = $(this).closest('li');

    card.find('input:checked').each(function () {
        data.push({
            labelmenu: $(this).parent().text().trim(),
            item_id: this.value,
            type: 'page',
            link: location.origin,
            idmenu: $('#idmenu').val()
        });
    });

    if (data.length) {
        $.post("{{ route('hcustomitem') }}", { data })
         .done(() => location.reload());
    }
});

// SELECT ALL
$(document).on('change', '.select_all', function () {
    $(this).closest('li').find('input[type=checkbox]').prop('checked', this.checked);
});

// SEARCH
$(document).on('input', '.searchInput', function () {
    let val = this.value.toLowerCase();
    $(this).siblings('.checkbox-wrapper').find('.checkbox').each(function () {
        $(this).toggle($(this).data('value').toLowerCase().includes(val));
    });
});
</script>
@endpush
