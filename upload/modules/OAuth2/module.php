<?php
/*
 *  Made by Partydragen
 *  https://github.com/partydragen/Nameless-OAuth2
 *  NamelessMC version 2.2.0
 *
 *  License: MIT
 *
 *  OAuth2 module file
 */

class OAuth2_Module extends Module {
    private DB $_db;
    private $_oauth2_language, $_language;

    public function __construct(Language $language, Language $oauth2_language, Pages $pages, Cache $cache, Endpoints $endpoints){
        $this->_db = DB::getInstance();
        $this->_language = $language;
        $this->_oauth2_language = $oauth2_language;

        $name = 'OAuth2';
        $author = '<a href="https://partydragen.com/" target="_blank" rel="nofollow noopener">Partydragen</a>';
        $module_version = '1.1.0';
        $nameless_version = '2.2.0';

        parent::__construct($this, $name, $author, $module_version, $nameless_version);

        // Define URLs which belong to this module
        $pages->add('OAuth2', '/oauth2/authorize', 'pages/oauth2.php');
        $pages->add('OAuth2', '/user/applications', 'pages/user/applications.php');
        $pages->add('OAuth2', '/panel/applications', 'pages/panel/applications.php');

        // Check if module version changed
        $cache->setCache('oauth2_module_cache');
        if (!$cache->isCached('module_version')) {
            $cache->store('module_version', $module_version);
        } else {
            if ($module_version != $cache->retrieve('module_version')) {
                // Version have changed, Perform actions
                $this->initialiseUpdate($cache->retrieve('module_version'));

                $cache->store('module_version', $module_version);

                if ($cache->isCached('update_check')) {
                    $cache->erase('update_check');
                }
            }
        }

        try {
            // Register integrations for namelessmc applications
            $applications = $this->_db->query("SELECT * FROM nl2_oauth2_applications WHERE nameless = 1 AND enabled = 1")->results();
            foreach ($applications as $app) {
                $application = new Application(null, null, $app);
                Integrations::getInstance()->registerIntegration(new ApplicationIntegration($language, $application));

                NamelessOAuth::getInstance()->registerProvider(strtolower($application->getName()), 'OAuth2', [
                    'class' => NamelessProvider::class,
                    'user_id_name' => 'id',
                    'scope_id_name' => 'identify',
                    'icon' => 'fa-solid fa-globe',
                    'verify_email' => static fn () => true,
                ]);

                // Register group sync for namelessmc application if enabled
                if ($application->data()->group_sync) {
                    GroupSyncManager::getInstance()->registerInjector(new ApplicationGroupSyncInjector($application));
                }
            }
        } catch (Exception $e) {
            // Database tables don't exist yet
        }

        OAuth2::registerScope('identify', 'Your username');
        OAuth2::registerScope('email', 'Your email address');

        if (Util::isModuleEnabled('Resources')) {
            OAuth2::registerScope('resources.licenses', 'Read your resource licenses');
        }

        $endpoints->loadEndpoints(ROOT_PATH . '/modules/OAuth2/includes/endpoints');
    }

    public function onInstall() {
        // Initialise
        $this->initialise();
    }

    public function onUninstall() {
        // Nothing to do here
    }

    public function onEnable() {
        // Check if we need to initialise again
        $this->initialise();
    }

    public function onDisable() {
        // Nothing to do here
    }

    public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template) {
        if (defined('BACK_END')) {
            // Define permissions which belong to this module
            PermissionHandler::registerPermissions('OAuth2', [
                'oauth2.applications' => $this->_language->get('moderator', 'staff_cp') . ' &raquo; ' . $this->_oauth2_language->get('general', 'applications'),
            ]);

            if ($user->hasPermission('oauth2.applications')) {
                $cache->setCache('panel_sidebar');
                if (!$cache->isCached('oauth2_order')) {
                    $order = 99;
                    $cache->store('oauth2_order', 99);
                } else {
                    $order = $cache->retrieve('oauth2_order');
                }
                $navs[2]->add('oauth2_divider', mb_strtoupper($this->_oauth2_language->get('general', 'oauth2'), 'UTF-8'), 'divider', 'top', null, $order, '');

                if ($user->hasPermission('oauth2.applications')) {
                    if (!$cache->isCached('oauth2_applications_icon')) {
                        $icon = '<i class="nav-icon fas fa-cogs"></i>';
                        $cache->store('oauth2_applications_icon', $icon);
                    } else {
                        $icon = $cache->retrieve('oauth2_applications_icon');
                    }
                    $navs[2]->add('applications', $this->_oauth2_language->get('general', 'applications'), URL::build('/panel/applications'), 'top', null, $order + 0.1, $icon);
                }
            }
        }

        // Check for module updates
        if (isset($_GET['route']) && $user->isLoggedIn() && $user->hasPermission('admincp.update')) {
            // Page belong to this module?
            $page = $pages->getActivePage();
            if ($page['module'] == 'OAuth2') {

                $cache->setCache('oauth2_module_cache');
                if ($cache->isCached('update_check')) {
                    $update_check = $cache->retrieve('update_check');
                } else {
                    $update_check = OAuth2_Module::updateCheck();
                    $cache->store('update_check', $update_check, 3600);
                }

                $update_check = json_decode($update_check);
                if (!isset($update_check->error) && !isset($update_check->no_update) && isset($update_check->new_version)) {  
                    $template->getEngine()->addVariables([
                        'NEW_UPDATE' => (isset($update_check->urgent) && $update_check->urgent == 'true') ? $this->_oauth2_language->get('general', 'new_urgent_update_available_x', ['module' => $this->getName()]) : $this->_oauth2_language->get('general', 'new_update_available_x', ['module' => $this->getName()]),
                        'NEW_UPDATE_URGENT' => (isset($update_check->urgent) && $update_check->urgent == 'true'),
                        'CURRENT_VERSION' => $this->_oauth2_language->get('general', 'current_version_x', [
                            'version' => Output::getClean($this->getVersion())
                        ]),
                        'NEW_VERSION' => $this->_oauth2_language->get('general', 'new_version_x', [
                            'new_version' => Output::getClean($update_check->new_version)
                        ]),
                        'NAMELESS_UPDATE' => $this->_oauth2_language->get('general', 'view_resource'),
                        'NAMELESS_UPDATE_LINK' => Output::getClean($update_check->link)
                    ]);
                }
            }
        }
    }

    public function getDebugInfo(): array {
        $applications_list = [];
        $applications = $this->_db->query("SELECT * FROM nl2_oauth2_applications WHERE nameless = 1 AND enabled = 1")->results();
        foreach ($applications as $app) {
            $application = new Application(null, null, $app);

            $applications_list[] = [
                'id' => $application->data()->id,
                'user_id' => $application->data()->user_id,
                'client_id' => $application->data()->client_id,
                'redirect_uri' => $application->data()->redirect_uri,
                'nameless' => $application->data()->nameless,
                'nameless_url' => $application->data()->nameless_url,
                'nameless_client_id' => $application->data()->nameless_client_id,
                'nameless_api_key' => $application->data()->nameless_api_key,
                'group_sync' => $application->data()->group_sync,
                'sync_integrations' => $application->data()->sync_integrations,
                'skip_approval' => $application->data()->skip_approval,
                'enabled' => $application->data()->enabled,
            ];
        }

        return [
            'applications' => $applications_list
        ];
    }

    private function initialiseUpdate($old_version) {
        $old_version = str_replace([".", "-"], "", $old_version);

        if ($old_version < 110) {
            try {
                DB::getInstance()->query("ALTER TABLE `nl2_oauth2_applications` ADD `skip_approval` tinyint(1) NOT NULL DEFAULT '0'");
                DB::getInstance()->query("ALTER TABLE `nl2_oauth2_applications` ADD `sync_integrations` tinyint(1) NOT NULL DEFAULT '0'");
            } catch (Exception $e) {
                // Error
            }

            try {
                DB::getInstance()->query("ALTER TABLE `nl2_oauth2_tokens` ADD `last_used` int(11) DEFAULT NULL");
                DB::getInstance()->query("ALTER TABLE `nl2_oauth2_tokens` ADD `scopes` varchar(1024) NOT NULL");
            } catch (Exception $e) {
                // Error
            }
        }
    }

    private function initialise() {
        if (!$this->_db->showTables('oauth2_applications')) {
            try {
                $this->_db->createTable("oauth2_applications", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `name` varchar(32) NOT NULL, `client_id` varchar(64) NOT NULL, `client_secret` varchar(64) NOT NULL, `redirect_uri` varchar(128) NOT NULL, `created` int(11) NOT NULL, `nameless` tinyint(1) NOT NULL DEFAULT '0', `nameless_url` varchar(128) NULL DEFAULT NULL, `nameless_client_id` varchar(64) NULL DEFAULT NULL, `nameless_api_key` varchar(64) NULL DEFAULT NULL, `group_sync` tinyint(1) NOT NULL DEFAULT '0', `sync_integrations` tinyint(1) NOT NULL DEFAULT '0', `skip_approval` tinyint(1) NOT NULL DEFAULT '0', `enabled` tinyint(1) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)");
            } catch (Exception $e) {
                // Error
                die($e);
            }
        }

        if (!$this->_db->showTables('oauth2_tokens')) {
            try {
                $this->_db->createTable("oauth2_tokens", " `id` int(11) NOT NULL AUTO_INCREMENT, `application_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `code` varchar(64) NOT NULL, `access_token` varchar(64) NOT NULL, `refresh_token` varchar(64) NOT NULL, `scopes` varchar(1024) NOT NULL, `created` int(11) NOT NULL, `last_used` int(11) DEFAULT NULL, PRIMARY KEY (`id`)");
            } catch (Exception $e) {
                // Error
                die($e);
            }
        }
    }

    /*
     *  Check for Module updates
     *  Returns JSON object with information about any updates
     */
    private static function updateCheck() {
        $current_version = Settings::get('nameless_version');
        $uid = Settings::get('unique_id');

        $enabled_modules = Module::getModules();
        foreach ($enabled_modules as $enabled_item) {
            if ($enabled_item->getName() == 'OAuth2') {
                $module = $enabled_item;
                break;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, 'https://api.partydragen.com/stats.php?uid=' . $uid . '&version=' . $current_version . '&module=OAuth2&module_version='.$module->getVersion() . '&domain='. URL::getSelfURL());

        $update_check = curl_exec($ch);
        curl_close($ch);

        $info = json_decode($update_check);
        if (isset($info->message)) {
            die($info->message);
        }

        return $update_check;
    }
}