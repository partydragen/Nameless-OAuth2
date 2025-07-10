<?php
/*
 *  Made by Partydragen
 *  https://github.com/partydragen/Nameless-OAuth2
 *  NamelessMC version 2.2.0
 *
 *  License: MIT
 *
 *  OAuth2 module - User applications page
 */

// Must be logged in
if(!$user->isLoggedIn()){
    Redirect::to(URL::build('/'));
}

if (!$user->hasPermission('usercp.oauth2.applications')) {
    Redirect::to(URL::build('/user'));
}

// Always define page name for navbar
const PAGE = 'cc_applications';
$page_title = $language->get('user', 'user_cp');
require_once(ROOT_PATH . '/core/templates/frontend_init.php');

if (!isset($_GET['action'])) {
    // List applications
    $applications = DB::getInstance()->query('SELECT * FROM nl2_oauth2_applications WHERE user_id = ?', [$user->data()->id]);
    if ($applications->count()) {
        $applications_list = [];

        foreach ($applications->results() as $item) {
            $application = new Application(null, null, $item);

            $applications_list[] = [
                'id' => $application->data()->id,
                'name' => Output::getClean($application->data()->name),
                'edit_link' => URL::build('/user/applications/', 'action=view&app=' . $application->data()->client_id),
            ];
        }

        $template->getEngine()->addVariable('APPLICATIONS_LIST', $applications_list);
    }

    if (!isset($applications_list)) {
        $template->getEngine()->addVariables([
            'NEW_APPLICATION' => $oauth2_language->get('general', 'new_application'),
            'NEW_APPLICATION_LINK' => URL::build('/user/applications/', 'action=new')
        ]);
    }

    $template->getEngine()->addVariables([
        'NO_APPLICATIONS' => $oauth2_language->get('general', 'user_no_applications'),
        'APPLICATIONS_LIST' => $applications_list,
        'VIEW' => $language->get('general', 'view')
    ]);

    $template_file = 'oauth2/user/applications';
} else {
    switch ($_GET['action']) {
        case 'new':
            // Create new application
            if (!$user->hasPermission('usercp.oauth2.applications.new')) {
                Redirect::to(URL::build('/user'));
            }

            if (Input::exists()) {
                $errors = [];

                if (Token::check(Input::get('token'))) {
                    // Validate input
                    $validation = Validate::check($_POST, [
                        'name' => [
                            Validate::REQUIRED => true,
                            Validate::MIN => 1,
                            Validate::MAX => 32
                        ],
                        'redirect_uri' => [
                            Validate::REQUIRED => true,
                            Validate::MIN => 12,
                            Validate::MAX => 128
                        ]
                    ]);

                    if ($validation->passed()) {
                        // Create application
                        try {
                            // Save to database
                            DB::getInstance()->insert('oauth2_applications', [
                                'user_id' => $user->data()->id,
                                'name' => Input::get('name'),
                                'client_id' => SecureRandom::alphanumeric(),
                                'client_secret' => SecureRandom::alphanumeric(),
                                'redirect_uri' => Input::get('redirect_uri'),
                                'created' => date('U')
                            ]);
                            $application = new Application(DB::getInstance()->lastId());

                            Session::flash('user_applications_success', $oauth2_language->get('general', 'application_created_successfully'));
                            Redirect::to(URL::build('/user/applications/', 'action=view&app=' . $application->data()->client_id));
                        } catch (Exception $e) {
                            $errors[] = $e->getMessage();
                        }
                    } else {
                        // Validation Errors
                        $errors = $validation->errors();
                    }
                } else {
                    $errors[] = $language->get('general', 'invalid_token');
                }
            }

            $template->getEngine()->addVariables([
                'NEW_APPLICATION' => $oauth2_language->get('general', 'new_application'),
                'APPLICATION_NAME' => $oauth2_language->get('general', 'application_name'),
                'REDIRECT_URI' => $oauth2_language->get('general', 'redirect_uri'),
            ]);

            $template_file = 'oauth2/user/applications_new';
            break;

        case 'view':
            // View application
            if (!isset($_GET['app'])) {
                Redirect::to(URL::build('/user/applications'));
            }

            $application = new Application($_GET['app'], 'client_id');
            if (!$application->exists()) {
                Redirect::to(URL::build('/user/applications'));
            }

            // Check application ownership
            if ($application->data()->user_id != $user->data()->id) {
                Redirect::to(URL::build('/user/applications'));
            }

            if (Input::exists()) {
                $errors = [];

                if (Token::check(Input::get('token'))) {
                    if (Input::get('action') == 'general') {
                        // Validate input
                        $validation = Validate::check($_POST, [
                            'name' => [
                                Validate::REQUIRED => true,
                                Validate::MIN => 1,
                                Validate::MAX => 32
                            ],
                            'redirect_uri' => [
                                Validate::REQUIRED => true,
                                Validate::MIN => 12,
                                Validate::MAX => 128
                            ]
                        ]);

                        if ($validation->passed()) {
                            // Create application
                            try {
                                // Save to database
                                DB::getInstance()->update('oauth2_applications', $application->data()->id, [
                                    'name' => Input::get('name'),
                                    'redirect_uri' => Input::get('redirect_uri')
                                ]);

                                Session::flash('user_applications_success', $oauth2_language->get('general', 'application_updated_successfully'));
                                Redirect::to(URL::build('/user/applications/', 'action=view&app=' . $application->data()->client_id));
                            } catch (Exception $e) {
                                $errors[] = $e->getMessage();
                            }
                        } else {
                            // Validation Errors
                            $errors = $validation->errors();
                        }
                    } else if (Input::get('action') == 'regen') {
                        // Regenerate secret key
                        $application->update([
                            'client_secret' => SecureRandom::alphanumeric(),
                        ]);

                        Session::flash('user_applications_success', $oauth2_language->get('general', 'client_secret_key_regenerated'));
                        Redirect::to(URL::build('/user/applications/', 'action=view&app=' . $application->data()->client_id));
                    }
                } else {
                    $errors[] = $language->get('general', 'invalid_token');
                }
            }

            $scopes_list = [];
            foreach (OAuth2::getScopes() as $key => $value) {
                $scopes_list[$key] = Output::getClean($value);
            }

            $template->getEngine()->addVariables([
                'EDITING_APPLICATION' => $oauth2_language->get('general', 'editing_application_x', [
                    'application' => Output::getClean($application->data()->name),
                ]),
                'CLIENT_ID' => $oauth2_language->get('general', 'client_id'),
                'CLIENT_ID_VALUE' => Output::getClean($application->data()->client_id),
                'CLIENT_SECRET' => $oauth2_language->get('general', 'client_secret'),
                'CLIENT_SECRET_VALUE' => Output::getClean($application->data()->client_secret),
                'APPLICATION_NAME' => $oauth2_language->get('general', 'application_name'),
                'APPLICATION_NAME_VALUE' => Output::getClean($application->data()->name),
                'REDIRECT_URI' => $oauth2_language->get('general', 'redirect_uri'),
                'REDIRECT_URI_VALUE' => Output::getClean($application->data()->redirect_uri),
                'OAUTH2_URL_GENERATOR' => $oauth2_language->get('general', 'oauth2_url_generator'),
                'OAUTH2_URL' => $oauth2_language->get('general', 'oauth2_url'),
                'OAUTH2_URL_VALUE' => $application->getAuthURL([]),
                'SELECT_SCOPES_TO_GENERATE' => $oauth2_language->get('general', 'select_scopes_to_generate'),
                'SCOPES' => $oauth2_language->get('general', 'scopes'),
                'SCOPES_LIST' => $scopes_list,
                'REGEN' => $oauth2_language->get('general', 'regen'),
                'ARE_YOU_SURE' => $language->get('general', 'are_you_sure'),
                'CONFIRM_SECRET_REGEN' => $oauth2_language->get('general', 'confirm_secret_regen'),
                'YES' => $language->get('general', 'yes'),
                'NO' => $language->get('general', 'no')
            ]);

            $template_file = 'oauth2/user/applications_form';
            break;

        default:
            Redirect::to(URL::build('/user/applications'));
            break;
    }
}

$template->getEngine()->addVariables([
    'APPLICATIONS' => $oauth2_language->get('general', 'applications'),
    'SUBMIT' => $language->get('general', 'submit'),
    'TOKEN' => Token::get(),
]);

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

if (Session::exists('user_applications_success')) {
    $success = Session::flash('user_applications_success');
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

require(ROOT_PATH . '/core/templates/cc_navbar.php');

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');

// Display template
$template->displayTemplate($template_file);