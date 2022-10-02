<?php
/*
 *  Made by Partydragen
 *  https://github.com/partydragen/Nameless-OAuth2
 *  NamelessMC version 2.0.2
 *
 *  License: MIT
 *
 *  OAuth2 module file
 */

class OAuth2_Module extends Module {
    private DB $_db;
    private $_patreon_language, $_language;

    public function __construct(Language $language, Language $oauth2_language, Pages $pages, Endpoints $endpoints){
        $this->_db = DB::getInstance();
        $this->_language = $language;
        $this->_oauth2_language = $oauth2_language;

        $name = 'OAuth2';
        $author = '<a href="https://partydragen.com/" target="_blank" rel="nofollow noopener">Partydragen</a>';
        $module_version = '1.0.0';
        $nameless_version = '2.0.2';

        parent::__construct($this, $name, $author, $module_version, $nameless_version);

        // Define URLs which belong to this module
        $pages->add('OAuth2', '/oauth2/authorize', 'pages/oauth2.php');
        $pages->add('OAuth2', '/panel/applications', 'pages/panel/applications.php');

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
                    'icon' => 'fab fa-browser',
                ]);

                // Register group sync for namelessmc application if enabled
                if ($application->data()->group_sync) {
                    GroupSyncManager::getInstance()->registerInjector(new ApplicationGroupSyncInjector($application));
                }
            }
        } catch (Exception $e) {
            // Database tables don't exist yet
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
    }

    public function getDebugInfo(): array {
        return [];
    }

    private function initialise() {
        if (!$this->_db->showTables('oauth2_applications')) {
            try {
                $this->_db->createTable("oauth2_applications", " `id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `name` varchar(32) NOT NULL, `client_id` varchar(64) NOT NULL, `client_secret` varchar(64) NOT NULL, `redirect_uri` varchar(128) NOT NULL, `created` int(11) NOT NULL, `nameless` tinyint(1) NOT NULL DEFAULT '0', `nameless_url` varchar(128) NULL DEFAULT NULL, `nameless_client_id` varchar(64) NULL DEFAULT NULL, `nameless_api_key` varchar(64) NULL DEFAULT NULL, `group_sync` tinyint(1) NOT NULL DEFAULT '0', `enabled` tinyint(1) NOT NULL DEFAULT '1', PRIMARY KEY (`id`)");
            } catch (Exception $e) {
                // Error
                die($e);
            }
        }

        if (!$this->_db->showTables('oauth2_tokens')) {
            try {
                $this->_db->createTable("oauth2_tokens", " `id` int(11) NOT NULL AUTO_INCREMENT, `application_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `code` varchar(64) NOT NULL, `access_token` varchar(64) NOT NULL, `refresh_token` varchar(64) NOT NULL, `created` int(11) NOT NULL, PRIMARY KEY (`id`)");
            } catch (Exception $e) {
                // Error
                die($e);
            }
        }
    }
}