<script>
	var menus = {
		"oneThemeLocationNoMenus" : "",
		"moveUp" : "Move up",
		"moveDown" : "Mover down",
		"moveToTop" : "Move top",
		"moveUnder" : "Move under of %s",
		"moveOutFrom" : "Out from under  %s",
		"under" : "Under %s",
		"outFrom" : "Out from %s",
		"menuFocus" : "%1$s. Element menu %2$d of %3$d.",
		"subMenuFocus" : "%1$s. Menu of subelement %2$d of %3$s."
	};
	var arraydata = [];     
	var addcustommenur= '{{ route("baddcustommenu") }}';
	var addcategorymenur= '{{ route("baddcategorymenu") }}';
	var addpostmenur= '{{ route("baddpostmenu") }}';
	var updateitemr= '{{ route("bupdateitem")}}';
	var generatemenucontrolr= '{{ route("bgeneratemenucontrol") }}';
	var deleteitemmenur= '{{ route("bdeleteitemmenu") }}';
	var deletemenugr= '{{ route("bdeletemenug") }}';
	var createnewmenur= '{{ route("bcreatenewmenu") }}';
	var csrftoken="{{ csrf_token() }}";
	var menuwr = "{{ url()->current() }}";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': csrftoken
		}
	});
</script>
<script type="text/javascript" src="{{asset('vendor/laravel-menu/scripts.js')}}"></script>
<script type="text/javascript" src="{{asset('vendor/laravel-menu/scripts2.js')}}"></script>
<script type="text/javascript" src="{{asset('vendor/laravel-menu/menu.js')}}"></script>
