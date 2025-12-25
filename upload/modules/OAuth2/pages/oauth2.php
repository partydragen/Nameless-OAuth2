<?php
/*
 *  Made by Partydragen
 *  https://github.com/partydragen/Nameless-OAuth2
 *  https://partydragen.com/
 *
 *  OAuth2 page
 */

// Always define page name for navbar
define('PAGE', 'oauth2');
$page_title = $oauth2_language->get('general', 'oauth2');
require_once(ROOT_PATH . '/core/templates/frontend_init.php');

if (!isset($_GET['client_id'])) {
    require_once(ROOT_PATH . '/403.php');
    die();
}

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

$template->onPageLoad();

Session::put('application_client', $_GET['client_id']);

// Must be logged in
if (!$user->isLoggedIn()){
    Redirect::to(URL::build('/login'));
}

// Get application by client id
$application = new Application($_GET['client_id'], 'client_id');
if (!$application->exists()) {
    require_once(ROOT_PATH . '/403.php');
    die();
}

// Make sure redirect uri is set
if (empty($application->getRedirectURI())) {
    $errors[] = $oauth2_language->get('general', 'invalid_redirect_uri');
}

// Make sure redirect uri match
if (!isset($_GET['redirect_uri']) || $application->getRedirectURI() != $_GET['redirect_uri']) {
    $errors[] = $oauth2_language->get('general', 'invalid_redirect_uri');
}

// Get requested scopes
$requested_scopes = OAuth2::getScopesFromString($_GET['scope'] ?? '');
if (!count($requested_scopes)) {
    $errors[] = $oauth2_language->get('general', 'no_scopes_provided');
}

$state = $_GET['state'] ?? null;
$code_challenge = $_GET['code_challenge'] ?? null;
$code_challenge_method = $_GET['code_challenge_method'] ?? 'plain';
if (!in_array($code_challenge_method, ['plain', 'S256'])) {
    $errors[] = $oauth2_language->get('general', 'invalid_code_challenge');
}

// Skip user approval if enabled
if ($application->data()->skip_approval === 1) {
    // Generate a code
    $code = SecureRandom::alphanumeric();

    DB::getInstance()->insert('oauth2_tokens', [
        'application_id' => $application->data()->id,
        'user_id' => $user->data()->id,
        'code' => $code,
        'access_token' => SecureRandom::alphanumeric(),
        'refresh_token' => SecureRandom::alphanumeric(),
        'created' => date('U'),
        'expires' => strtotime('+3600 seconds'),
        'scopes' => implode(' ', array_keys($requested_scopes)),
        'code_challenge' => $code_challenge,
        'code_challenge_method' => $code_challenge_method ?: 'plain',
    ]);

    // Build redirect URI with code and state
    $redirect = $application->getRedirectURI() . (str_contains($application->getRedirectURI(), '?') ? '&' : '?') . 'code=' . $code;
    if ($state !== null) {
        $redirect .= '&state=' . urlencode($state);
    }

    Redirect::to($redirect);
}

if (!isset($errors)) {
    if (Input::exists()) {
        if (Token::check(Input::get('token'))) {
            // Generate a code
            $code = SecureRandom::alphanumeric();

            DB::getInstance()->insert('oauth2_tokens', [
                'application_id' => $application->data()->id,
                'user_id' => $user->data()->id,
                'code' => $code,
                'access_token' => SecureRandom::alphanumeric(),
                'refresh_token' => SecureRandom::alphanumeric(),
                'created' => date('U'),
                'expires' => strtotime('+3600 seconds'),
                'scopes' => implode(' ', array_keys($requested_scopes)),
                'code_challenge' => $code_challenge,
                'code_challenge_method' => $code_challenge_method ?: 'plain'
            ]);

            // Build redirect URI with code and state
            $redirect = $application->getRedirectURI() . (str_contains($application->getRedirectURI(), '?') ? '&' : '?') . 'code=' . $code;
            if ($state !== null) {
                $redirect .= '&state=' . urlencode($state);
            }

            Redirect::to($redirect);
        } else {
            // Invalid token
            $errors[] = $language->get('general', 'invalid_token');
        }
    }

    $access_to = [];
    foreach ($requested_scopes as $scope) {
        $access_to[] = $scope;
    }

    $template->getEngine()->addVariables([
        'APPLICATION_NAME' => Output::getClean($application->getName()),
        'APPLICATION_WANTS_ACCESS' => $oauth2_language->get('general', 'application_wants_access', [
            'application' => $application->getName(),
            'siteName' => SITE_NAME
        ]),
        'APPLICATION_WANTS_INFORMATION' => $oauth2_language->get('general', 'application_wants_information', [
            'application' => $application->getName()
        ]),
        'AUTHORIZE' => $oauth2_language->get('general', 'authorize'),
        'CANCEL' => $language->get('general', 'cancel'),
        'CANCEL_LINK' => URL::build('/'),
        'TOKEN' => Token::get(),
        'ACCESS_TO' => $access_to
    ]);
}

if (isset($success))
	$template->getEngine()->addVariables([
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	]);

if (isset($errors) && count($errors))
	$template->getEngine()->addVariables([
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	]);

require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');

// Display template
$template->displayTemplate('oauth2/oauth2');