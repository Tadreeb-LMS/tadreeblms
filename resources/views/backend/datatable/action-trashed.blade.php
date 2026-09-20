<style>
/* Align buttons inside table */
td .table-actions {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

/* Theme button */
td .btn-theme {
    background: linear-gradient(90deg, #2f3e74 0%, #c79a2d 100%) !important;
     border: none !important;
    color: #ffffff !important;
    border-radius: 6px;
    width: 50px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: all 0.3s ease;
    font-size : 15px
}

/* Hover */
td .btn-theme:hover {
    background-color: #26345f !important;
    border-color: #c79a2d !important;
    color: #ffffff !important;
}

/* Remove bootstrap effects */
td .btn-theme:focus,
td .btn-theme:active {
    box-shadow: none !important;
    outline: none !important;
}
</style>

<div class="table-actions">

    <form action="{{ route($route_label.'.restore', [$label => $value]) }}" method="POST">
        @csrf
        <button type="submit" class="btn-theme">
            <i class="fa fa-recycle"></i>
        </button>
    </form>

    <a
        href="{{ route($route_label.'.perma_del', [$label => $value]) }}"
        data-method="delete"
        data-trans-button-cancel="{{ __('buttons.general.cancel') }}"
        data-trans-button-confirm="Permanently Delete"
        data-trans-title="⚠️ Permanently Delete Course?"
        data-trans-html="This action will permanently delete this course and its associated data. This action cannot be undone."        
        title="Permanently Delete"
        class="btn-theme"
        style="cursor:pointer;"
        onclick="event.preventDefault(); $(this).find('form').submit();"
    >
        <form
            action="{{ route($route_label.'.perma_del', [$label => $value]) }}"
            method="POST"
            name="delete_item"
            style="display:none"
        >
            @csrf
            @method('DELETE')
        </form>

        <i class="fa fa-trash"></i>
    </a>

</div>
