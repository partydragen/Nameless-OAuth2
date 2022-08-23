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
$page_title = 'oauth2';
require_once(ROOT_PATH . '/core/templates/frontend_init.php');

// Must be logged in
if(!$user->isLoggedIn()){
	Redirect::to(URL::build('/login'));
}

if (!isset($_GET['code'])) {
    if (!isset($_GET['client_id'])) {
        require_once(ROOT_PATH . '/403.php');
        die();
    }

    $application = new Application($_GET['client_id'], 'client_id');
    if (!$application->exists()) {
        require_once(ROOT_PATH . '/403.php');
        die();
    }

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
                'created' => date('U')
            ]);

            Redirect::to($application->getRedirectURI() . "&code=$code");
        } else {
            // Invalid token
            $errors[] = $language->get('general', 'invalid_token');
        }
    }

    $smarty->assign(array(
        'TOKEN' => Token::get()
    ));

} else {
    $integration = Integrations::getInstance()->getIntegration('MC Server List');
    if ($integration->validateIdentifier($_GET['c']) && $integration->validateUsername($_GET['username'])) {
        // Success
        $integrationUser = new IntegrationUser($integration);
        $integrationUser->linkIntegration($user, $_GET['c'], $_GET['username'], true);

        $integrationUser->verifyIntegration();

        Session::flash('connections_success', $language->get('user', 'integration_linked', ['integration' => Output::getClean($integration->getName())]));
        Redirect::to(URL::build('/user/connections'));
    } else {
        // Validation errors
        Session::flash('connections_error', $integration->getErrors()[0]);
        Redirect::to(URL::build('/user/connections'));
    }
}

if(isset($success))
	$smarty->assign(array(
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	));

if(isset($errors) && count($errors))
	$smarty->assign(array(
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	));

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');

// Display template
$template->displayTemplate('oauth2.tpl', $smarty);