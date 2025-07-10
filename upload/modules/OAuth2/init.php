<?php
/*
 *  Made by Partydragen
 *  https://github.com/partydragen/Nameless-OAuth2
 *  NamelessMC version 2.2.0
 *
 *  License: MIT
 *
 *  OAuth2 initialisation file
 */

// Initialise forms language
$oauth2_language = new Language(ROOT_PATH . '/modules/OAuth2/language', LANGUAGE);

require_once(ROOT_PATH . '/modules/OAuth2/autoload.php');

require_once(ROOT_PATH . '/modules/OAuth2/module.php');
$module = new OAuth2_Module($language, $oauth2_language, $pages, $cache, $endpoints);