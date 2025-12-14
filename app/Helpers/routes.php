<?php
use Illuminate\Support\Collection;

if (!function_exists('include_route_files')) {
    /**
     * Include all route files from a directory.
     *
     * @param string $folder
     * @return void
     */
    function include_route_files($folder)
    {
        $directory = app_path($folder);

        if (!is_dir($directory)) {
            return;
        }

        $routeFiles = scandir($directory);

        foreach ($routeFiles as $file) {
            if (in_array($file, ['.', '..'])) continue;

            $path = $directory . '/' . $file;

            if (is_file($path)) {
                require $path;
            }
        }
    }
}





if (!function_exists('menuList')) {
    function menuList(Collection $items, int $parent = 0): array
    {
        $menu = [];

        foreach ($items as $item) {
            if ((int)$item->parent === $parent) {
                $children = menuList($items, $item->id);

                if ($children) {
                    $item->children = $children;
                }

                $menu[] = $item;
            }
        }

        return $menu;
    }
}

