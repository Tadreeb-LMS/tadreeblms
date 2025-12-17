<?php

namespace App\Http\Controllers\Backend;

use App\Models\Config;
use App\Models\Category;
use App\Models\Page;
use Harimayco\Menu\Models\MenuItems;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Harimayco\Menu\Facades\Menu;
use App\Http\Requests;
use App\Models\AdminMenuItem;
use Harimayco\Menu\Models\Menus;
use Illuminate\Support\Facades\Artisan;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        //dd("ss");
        $menu = Null;
        $menu_data = Null;
        if ($request->menu) {
            $menu = Menus::find($request->menu);
            $menu_data = json_decode($menu->value);
        }

        $menu_list = Menus::get();

        //dd( $menu_list );

        $pages = Page::where('published', '=', 1)->get();

        return view('backend.menu-manager.index', compact('menu', 'menu_data', 'menu_list', 'pages'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'menu_name' => ' required'
        ]);

        //Fetching Menu JSON
        $menu_list = \App\Models\Config::where('key', '=', 'menu_list')->first();
        $add_data = "";
        $menu_name = str_replace(array('\'', '"'), '', $request->menu_name);
        $menus = json_decode($menu_list->value);

        //dd( $menus);

        //======Checking if menu exist, adding if doesn't exist======//
        if (!$menus) {
            $new_menu_data = json_encode(['menu_name' => $menu_name]);
            $new_menu = new Config();
            $new_menu->key = str_slug($menu_name);
            $new_menu->value = $new_menu_data;
            $new_menu->save();
            $add_data = ["id" => $new_menu->id, "name" => str_slug($menu_name)];
        } else {
            foreach ($menus as $item) {
                if (str_slug($menu_name) == str_slug($item->name)) {
                    return back()->withFlashDanger(__('alerts.backend.menu-manager.exist'));
                } else {
                    $new_menu_data = json_encode(['menu_name' => $menu_name]);
                    $new_menu = new Config();
                    $new_menu->key = str_slug($menu_name);
                    $new_menu->value = $new_menu_data;
                    $new_menu->save();
                    $add_data = ["id" => $new_menu->id, "name" => str_slug($menu_name)];
                }
            }
        }

        //======Updating Menu JSON======//
        $menus[] = $add_data;
        $menu_list->value = json_encode($menus);
        $menu_list->save();

        return redirect(route('admin.menu-manager') . '?menu=' . $new_menu->id)->with('')->withFlashSuccess(__('alerts.backend.menu-manager.created'));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'menu_name' => ' required'
        ]);
        $menu = Config::findOrFail($request->menu_id);
        if ($menu) {
            $menu_data = json_decode($menu->value);
            if (str_slug($menu_data->menu_name) != str_slug($request->menu_name)) {
                $menu_data->menu_name = str_slug($request->menu_name);
                $menu->value = json_encode($menu_data);
                $menu->save();
                return redirect(route('admin.menu-manager') . '?menu=' . $menu->id)->with('')->withFlashSuccess(__('alerts.backend.menu-manager.updated'));
            } else {
                return redirect(route('admin.menu-manager') . '?menu=' . $menu->id)->with('')->withFlashSuccess(__('alerts.backend.menu-manager.exist'));
            }
        }
    }

    public function delete(Request $request)
    {
        $menu = Config::findOrFail($request->id);
        $menu_list = Config::where('key', '=', 'menu_list')->first();
        $menu_items = json_decode($menu_list->value);
        $menu_new = array();
        foreach ($menu_items as $item) {
            if ((int)$request->id != (int)$item->id) {
                $menu_new[] = $item;
            }
        }
        if (empty($menu_new)) {
            $menu_list->value = '{""}';
        } else {
            $menu_list->value = json_encode($menu_new);
        }
        $menu_list->save();
        $menu->delete();
        return redirect(route('admin.menu-manager'))->withFlashSuccess(__('alerts.backend.menu-manager.deleted'));

    }

    public function saveCustomMenu(Request $request)
    {
        foreach ($request->data as $item) {
            $type = "";

            if ($item['type'] == 'page') {
                $type = __('strings.backend.menu_manager.page');
            }
            $object = Page::find((int)$item['item_id']);
            $menuitem = new AdminMenuItem();
            $menuitem->label = $item['labelmenu'];
            $menuitem->link = $item['link'] . '/' . $object->slug;
            $menuitem->menu = $item['idmenu'];
            $menuitem->sort = AdminMenuItem::getNextSortRoot($item['idmenu']);
            $menuitem->save();
        }
    }

    public function createnewmenu()
    {
        $menu = new Menus();
        $menu->name = str_slug(request()->input("menuname"));
        $menu->save();
        return json_encode(array("resp" => $menu->id));
    }

    public function deleteitemmenu()
    {
        $menuitem = AdminMenuItem::find(request()->input("id"));

        $menuitem->delete();
    }

    public function deletemenug()
    {
        $menus = new AdminMenuItem();
        $getall = $menus->getall(request()->input("id"));
        if (count($getall) == 0) {
            $menudelete = Menus::find(request()->input("id"));
            $menudelete->delete();

            return json_encode(array("resp" => "you delete this item"));
        } else {
            return json_encode(array("resp" => "You have to delete all items first", "error" => 1));

        }
    }

    public function updateitem()
    {
        $arraydata = request()->input("arraydata");
        //dd( $arraydata );
        if (is_array($arraydata)) {
            foreach ($arraydata as $value) {
                $menuitem = AdminMenuItem::find($value['id']);
                $menuitem->label = $value['label'];
                //$menuitem->label_ar = $value['label_ar'];
                $menuitem->link = $value['link'];
                $menuitem->class = $value['class'];
                $menuitem->save();
            }
        } else {
            $menuitem = AdminMenuItem::find(request()->input("id"));
            $menuitem->label = request()->input("label");
            //$menuitem->label_ar = request()->input("label_ar");
            $menuitem->link = request()->input("url");
            $menuitem->class = request()->input("class");
            $menuitem->save();
        }
    }


    public function addcustommenu()
    {

        $menuitem = new AdminMenuItem();
        $menuitem->label = request()->input("labelmenu");
        $menuitem->link = request()->input("linkmenu");
        $menuitem->menu = request()->input("idmenu");
//        $menuitem->type = __('strings.backend.menu_manager.link');
        $menuitem->sort = AdminMenuItem::getNextSortRoot(request()->input("idmenu"));
        $menuitem->save();

    }

    public function generatemenucontrol(Request $request)
    {
        $main = NULL;
        $menu = Menus::find(request()->input("idmenu"));
        $menu_bag_data = AdminMenuItem::where('menu', '=', $menu)->get();
        $menu->name = str_slug(request()->input("menuname"));
        $menu->save();
        $value = 0;
        if (request('meta')[0]['nav_menu'] == 'true') {
            $value = $menu->id;
        } else {
            $value = 0;
        }
        $config = \App\Models\Config::where('key', '=', 'nav_menu')->first();
        $config->value = $value;
        $config->save();

//        dd(json_decode($menu_bag->value));
        if (is_array(request()->input("arraydata"))) {
            foreach (request()->input("arraydata") as $value) {
                $menuitem = AdminMenuItem::find($value["id"]);
                $menuitem->parent = $value["parent"];
                $menuitem->sort = $value["sort"];
                $menuitem->depth = $value["depth"];
                $menuitem->save();
            }
        }
        $menus = \Harimayco\Menu\Models\Menus::all();
        foreach ($menus as $menu) {
            if ($menu != NULL) {
                $menuItems = AdminMenuItem::where('menu', '=', $menu->id)->get();
                if ($menuItems != null) {
                    $allMenu = [];
                    foreach ($menuItems as $item) {
                        $allMenu[str_slug($item['label'])] = $item['label'];
                    }
                    $main[str_slug($menu->name)] = $allMenu;
                    $file = fopen(public_path('../resources/lang/en/custom-menu.php'), 'w');
                    if ($file !== false) {
                        ftruncate($file, 0);
                    }
                    fwrite($file, '<?php return ' . var_export($main, true) . ';');
                    Artisan::call('menu:import');
                }
            }
        }
        return json_encode(array("resp" => 1));

    }

    public function updateLocation(Request $request)
    {
        $menu_list = \App\Models\Config::where('key', '=', 'menu_list')->first();
        $menu_bag = json_decode($menu_list->value);
        foreach ($menu_bag as $menu) {
            if ($menu->location == 'top_menu') {
                $menu->id = ($request->location_top_menu == "") ? 0 : $request->location_top_menu;
            } else if ($menu->location == 'footer_menu') {
                $menu->id = ($request->location_top_menu == "") ? 0 : $request->location_footer_menu;
            }
        }
        json_encode($menu_bag);
        $menu_list->value = json_encode($menu_bag);
        $menu_list->save();
        return back();
    }
}
