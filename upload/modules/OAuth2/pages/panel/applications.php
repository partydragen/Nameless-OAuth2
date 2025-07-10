<?php 
/*
 *  Made by Partydragen
 *  https://github.com/partydragen/Nameless-OAuth2
 *  NamelessMC version 2.2.0
 *
 *  License: MIT
 *
 *  OAuth2 module - panel form page
 */

// Can the user view the panel?
if (!$user->handlePanelPageLoad('oauth2.applications')) {
    require_once(ROOT_PATH . '/403.php');
    die();
}

define('PAGE', 'panel');
define('PARENT_PAGE', 'applications');
define('PANEL_PAGE', 'applications');
$page_title = $oauth2_language->get('general', 'applications');
require_once(ROOT_PATH . '/core/templates/backend_init.php');

if (!isset($_GET['action'])) {
    // View all applications
    $applications_list = [];
    $applications = DB::getInstance()->get('oauth2_applications', ['id', '<>', 0])->results();
    foreach ($applications as $app) {
        $applications_list[] = [
            'name' => Output::getClean($app->name),
            'edit_link' => URL::build('/panel/applications/', 'action=edit&app='.$app->id)
        ];
    }

    $template->getEngine()->addVariables([
        'APPLICATIONS_LIST' => $applications_list,
        'NEW_APPLICATION' => $oauth2_language->get('general', 'new_application'),
        'NEW_APPLICATION_LINK' => URL::build('/panel/applications/', 'action=new'),
        'NO_APPLICATIONS' => $oauth2_language->get('general', 'no_applications'),
    ]);

    $template_file = 'oauth2/applications';
} else {
    switch($_GET['action']) {
        case 'new':
            // New Application
            if (Input::exists()) {
                $errors = [];

                if (Token::check(Input::get('token'))) {
                    // Validate input
                    $validation = Validate::check($_POST, [
                        'name' => [
                            Validate::REQUIRED => true,
                            Validate::MIN => 1,
                            Validate::MAX => 32
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

                            Session::flash('staff_applications', $oauth2_language->get('general', 'application_created_successfully'));
                            Redirect::to(URL::build('/panel/applications/', 'action=edit&app=' . $application->data()->id));
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
                'APPLICATION_TITLE' => $oauth2_language->get('general', 'creating_application'),
                'BACK' => $language->get('general', 'back'),
                'BACK_LINK' => URL::build('/panel/applications/'),
                'NAME' => $language->get('admin', 'name'),
                'NAME_VALUE' => Output::getClean(Input::get('name')),
                'REDIRECT_URI' => $oauth2_language->get('general', 'redirect_uri'),
                'REDIRECT_URI_VALUE' => Output::getClean(Input::get('redirect_uri'))
            ]);
        
            $template_file = 'oauth2/applications_new';
        break;
        case 'edit':
            if (!isset($_GET['app']) || !is_numeric($_GET['app'])) {
                Redirect::to(URL::build('/panel/applications'));
            }

            $application = new Application($_GET['app']);

            // Edit Field
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
                        'nameless_client_id' => [
                            Validate::MIN => 32,
                            Validate::MAX => 64
                        ],
                        'nameless_api_key' => [
                            Validate::MIN => 32,
                            Validate::MAX => 64
                        ]
                    ]);

                    if ($validation->passed()) {
                        // Update application
                        try {
                            // NamelessMC Integration
                            $nameless_integration = (isset($_POST['nameless_integration']) && $_POST['nameless_integration'] == 'on') ? '1' : '0';
                            $sync_groups = (isset($_POST['sync_groups']) && $_POST['sync_groups'] == 'on') ? '1' : '0';
                            $sync_integrations = (isset($_POST['sync_integrations']) && $_POST['sync_integrations'] == 'on') ? '1' : '0';
                            $skip_approval = (isset($_POST['skip_approval']) && $_POST['skip_approval'] == 'on') ? '1' : '0';

                            if ($sync_groups) {
                                $column = 'app_' . $application->data()->id . '_group_id';

                                try {
                                    DB::getInstance()->query("ALTER TABLE `nl2_group_sync` ADD $column int(11) DEFAULT NULL");
                                } catch (Exception $e) {
                                    // Error
                                }
                            }

                            // Save to database
                            $application->update([
                                'name' => Input::get('name'),
                                'redirect_uri' => Input::get('redirect_uri'),
                                'nameless' => $nameless_integration,
                                'nameless_url' => !empty(Input::get('nameless_url')) ? rtrim(Input::get('nameless_url'), '/') : null,
                                'nameless_client_id' => !empty(Input::get('nameless_client_id')) ? Input::get('nameless_client_id') : null,
                                'nameless_api_key' => !empty(Input::get('nameless_api_key')) ? Input::get('nameless_api_key') : null,
                                'skip_approval' => $skip_approval,
                                'group_sync' => $sync_groups,
                                'sync_integrations' => $sync_integrations
                            ]);

                            Session::flash('staff_applications', $oauth2_language->get('general', 'application_updated_successfully'));
                            Redirect::to(URL::build('/panel/applications/', 'action=edit&app=' . $application->data()->id));
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

            $scopes_list = [];
            foreach (OAuth2::getScopes() as $key => $value) {
                $scopes_list[$key] = Output::getClean($value);
            }

            $template->getEngine()->addVariables([
                'APPLICATION_TITLE' => $oauth2_language->get('general', 'editing_application_x', ['application' => Output::getClean($application->data()->name)]),
                'BACK' => $language->get('general', 'back'),
                'BACK_LINK' => URL::build('/panel/applications/'),
                'NAME' => $language->get('admin', 'name'),
                'NAME_VALUE' => Output::getClean($application->data()->name),
                'REDIRECT_URI' => $oauth2_language->get('general', 'redirect_uri'),
                'REDIRECT_URI_VALUE' => Output::getClean($application->data()->redirect_uri),
                'CLIENT_ID_VALUE' => Output::getClean($application->data()->client_id),
                'CLIENT_SECRET_VALUE' => Output::getClean($application->data()->client_secret),
                'NAMELESS_INTEGRATION_VALUE' => Output::getClean($application->data()->nameless),
                'NAMELESS_URL_VALUE' => Output::getClean($application->data()->nameless_url),
                'NAMELESS_CLIENT_ID_VALUE' => Output::getClean($application->data()->nameless_client_id),
                'NAMELESS_API_KEY_VALUE' => Output::getClean($application->data()->nameless_api_key),
                'SKIP_APPROVAL_VALUE' => Output::getClean($application->data()->skip_approval),
                'SYNC_GROUPS_VALUE' => Output::getClean($application->data()->group_sync),
                'SYNC_INTEGRATIONS_VALUE' => Output::getClean($application->data()->sync_integrations),
                'CHANGE' => $language->get('general', 'change'),
                'COPY' => $language->get('admin', 'copy'),
                'COPIED' => $language->get('admin', 'copied'),
                'ARE_YOU_SURE' => $language->get('general', 'are_you_sure'),
                'CONFIRM_SECRET_REGEN' => $oauth2_language->get('general', 'confirm_secret_regen'),
                'YES' => $language->get('general', 'yes'),
                'NO' => $language->get('general', 'no'),
                'REGEN_CLIENT_SECRET_LINK' => URL::build('/panel/applications/', 'action=regen&app=' . $application->data()->id),
                'OAUTH2_URL_GENERATOR' => $oauth2_language->get('general', 'oauth2_url_generator'),
                'OAUTH2_URL' => $oauth2_language->get('general', 'oauth2_url'),
                'OAUTH2_URL_VALUE' => $application->getAuthURL([]),
                'SELECT_SCOPES_TO_GENERATE' => $oauth2_language->get('general', 'select_scopes_to_generate'),
                'SCOPES' => $oauth2_language->get('general', 'scopes'),
                'SCOPES_LIST' => $scopes_list
            ]);
        
            $template_file = 'oauth2/applications_edit';
        break;
        case 'regen':
            // Regenerate secret key
            if (!isset($_GET['app']) || !is_numeric($_GET['app'])) {
                 Redirect::to(URL::build('/panel/applications'));
            }

            $application = new Application($_GET['app']);
            if (Token::check()) {
                $application->update([
                    'client_secret' => SecureRandom::alphanumeric(),
                ]);

                Session::flash('staff_applications', $oauth2_language->get('general', 'client_secret_key_regenerated'));
            }

            Redirect::to(URL::build('/panel/applications/', 'action=edit&app=' . $application->data()->id));
        break;
        case 'delete':
            // Delete Field
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                Redirect::to(URL::build('/panel/forms'));
            }
            DB::getInstance()->update('forms_fields', $_GET['id'], [
                'deleted' => 1
            ]);
                
            Session::flash('staff_forms', $forms_language->get('forms', 'field_deleted_successfully'));
            Redirect::to(URL::build('/panel/form/', 'form='.$form->data()->id));
        break;
        default:
            Redirect::to(URL::build('/panel/applications'));
        break;
    }
}

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

if (Session::exists('staff_applications'))
    $success = Session::flash('staff_applications');

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

$template->getEngine()->addVariables([
    'PARENT_PAGE' => PARENT_PAGE,
    'PAGE' => PANEL_PAGE,
    'DASHBOARD' => $language->get('admin', 'dashboard'),
    'APPLICATIONS' => $oauth2_language->get('general', 'applications'),
    'TOKEN' => Token::get(),
    'SUBMIT' => $language->get('general', 'submit'),
]);

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/panel_navbar.php');

// Display template
$template->displayTemplate($template_file);