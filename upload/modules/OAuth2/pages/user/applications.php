<?php
/*
 *  Made by Partydragen
 *  https://github.com/partydragen/Nameless-OAuth2
 *  NamelessMC version 2.1.2
 *
 *  License: MIT
 *
 *  OAuth2 module - User applications page
 */

// Must be logged in
if(!$user->isLoggedIn()){
    Redirect::to(URL::build('/'));
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
                'edit_link' => URL::build('/user/applications/', 'action=view&app=' . $application->data()->id),
            ];
        }

        $smarty->assign('APPLICATIONS_LIST', $applications_list);
    }

    if (!isset($applications_list)) {
        $smarty->assign([
            'NEW_APPLICATION' => $oauth2_language->get('general', 'new_application'),
            'NEW_APPLICATION_LINK' => URL::build('/user/applications/', 'action=new')
        ]);
    }

    $smarty->assign([
        'NO_APPLICATIONS' => $oauth2_language->get('general', 'user_no_applications'),
        'APPLICATIONS_LIST' => $applications_list
    ]);

    $template_file = 'oauth2/user/applications.tpl';
} else {
    switch ($_GET['action']) {
        case 'new':
            // Create new application
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
                            Redirect::to(URL::build('/user/applications/', 'action=view&app=' . $application->data()->id));
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

            $smarty->assign([
                'NEW_APPLICATION' => $oauth2_language->get('general', 'new_application'),
                'APPLICATION_NAME' => $oauth2_language->get('general', 'application_name'),
                'REDIRECT_URI' => $oauth2_language->get('general', 'redirect_uri'),
            ]);

            $template_file = 'oauth2/user/applications_new.tpl';
            break;

        case 'view':
            // View application
            if (!isset($_GET['app']) || !is_numeric($_GET['app'])) {
                Redirect::to(URL::build('/user/applications'));
            }

            $application = new Application($_GET['app']);
            if (!$application->exists()) {
                Redirect::to(URL::build('/user/applications'));
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
                            DB::getInstance()->update('oauth2_applications', $application->data()->id, [
                                'name' => Input::get('name'),
                                'redirect_uri' => Input::get('redirect_uri')
                            ]);

                            Session::flash('user_applications_success', $oauth2_language->get('general', 'application_updated_successfully'));
                            Redirect::to(URL::build('/user/applications/', 'action=view&app=' . $application->data()->id));
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

            $smarty->assign([
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
            ]);

            $template_file = 'oauth2/user/applications_form.tpl';
            break;

        default:
            Redirect::to(URL::build('/user/applications'));
            break;
    }
}

$smarty->assign([
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
    $smarty->assign([
        'SUCCESS' => $success,
        'SUCCESS_TITLE' => $language->get('general', 'success')
    ]);

if (isset($errors) && count($errors))
    $smarty->assign([
        'ERRORS' => $errors,
        'ERRORS_TITLE' => $language->get('general', 'error')
    ]);

require(ROOT_PATH . '/core/templates/cc_navbar.php');

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');

// Display template
$template->displayTemplate($template_file, $smarty);