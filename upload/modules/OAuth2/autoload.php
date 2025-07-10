<?php
/*
 *  Made by Partydragen
 *  https://github.com/partydragen/Nameless-OAuth2
 *  NamelessMC version 2.2.0
 *
 *  License: MIT
 *
 *  OAuth2 autoload file
 */

// Load classes
spl_autoload_register(function ($class) {
    $path = join(DIRECTORY_SEPARATOR, array(ROOT_PATH, 'modules', 'OAuth2', 'classes', $class . '.php'));
    if (file_exists($path)) {
        require_once($path);
    }
});

// Load classes
spl_autoload_register(function ($class) {
    $path = join(DIRECTORY_SEPARATOR, array(ROOT_PATH, 'modules', 'OAuth2', 'classes', 'Provider', $class . '.php'));
    if (file_exists($path)) {
        require_once($path);
    }
});