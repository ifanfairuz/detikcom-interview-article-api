<?php

/**
 * load file
 */
function load_file($path)
{
    if (file_exists($path)) require_once($path);
    else throw new Exception("File $path not found.");
}

/**
 * implement minimum autoload
 */
spl_autoload_register(function ($class) {
    $exploded = explode('\\', $class);
    $base = array_shift($exploded);
    $path = join(DIRECTORY_SEPARATOR, $exploded) . ".php";

    switch ($base) {
        case 'App':
            load_file(app_dir($path));
            break;
        case 'Core':
            load_file(core_dir($path));
            break;
        default:
            throw new Exception("Class $class not found.");
            break;
    }
});
