<?php

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
