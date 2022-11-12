<?php
/*
 *  Made by Partydragen
 *  https://github.com/partydragen/Nameless-OAuth2
 *  NamelessMC version 2.0.2
 *
 *  License: MIT
 *
 *  OAuth2 initialisation file
 */
// Initialise forms language
$oauth2_language = new Language(ROOT_PATH . '/modules/OAuth2/language', LANGUAGE);

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

require_once(ROOT_PATH . '/modules/OAuth2/module.php');
$module = new OAuth2_Module($language, $oauth2_language, $pages, $cache, $endpoints);