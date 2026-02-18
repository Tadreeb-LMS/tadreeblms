<?php

use App\Models\Category;
use App\Models\Page;

return [
	/* you can add your own middleware here */
	
	'middleware' => [],

	/* you can set your own table prefix here */
	'table_prefix' => 'admin_',

    /* you can set your own table names */
    'table_name_menus' => 'menus',

    'table_name_items' => 'menu_items',

    /* you can set your route path*/
    'route_path' => '/user/',

    'category' => [
        'name' => 'name',
        'category_model' => Category::class,
        'prefix' => '/category/',
    ],

    'post' => [
        'name' => 'title',
        'post_model' => Page::class,
        'prefix' => '/page/',
    ],
];
