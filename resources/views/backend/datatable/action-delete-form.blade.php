<form action="{{ $route }}"
      method="POST"
      class="datatable-delete-form d-inline"
      onsubmit="return confirm(@json(__('strings.backend.general.are_you_sure')));">
    @csrf
    {{ method_field('DELETE') }}
    <button type="submit"
            class="datatable-delete-action"
            title="{{ __('buttons.general.crud.delete') }}"
            style="background: transparent; border: 0; padding: 0; color: inherit; cursor: pointer;">
        <i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ __('buttons.general.crud.delete') }}"></i>
    </button>
</form>
