<?php
/*
 *	Made by Partydragen
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

        try {
            // Register integrations for namelessmc applications
            $applications = $this->_db->query("SELECT * FROM nl2_oauth2_applications WHERE type = 'nameless' AND enabled = 1")->results();
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
                    //GroupSyncManager::getInstance()->registerInjector(new ApplicationGroupSyncInjector($application));
                }
            }
        } catch (Exception $e) {
            // Database tables don't exist yet
        }

        $endpoints->loadEndpoints(ROOT_PATH . '/modules/OAuth2/includes/endpoints');
	}

	public function onInstall(){
        // Initialise
        $this->initialise();
	}

	public function onUninstall(){
        // Nothing to do here
	}

	public function onEnable(){
        // Check if we need to initialise again
        $this->initialise();
	}

	public function onDisable(){
        // Nothing to do here
	}

	public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template){
        // Nothing to do here
	}

    public function getDebugInfo(): array {
        return [];
    }

    private function initialise() {

    }
}