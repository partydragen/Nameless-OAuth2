<?php
/**
 * ApplicationIntegration class
 *
 * @package Modules\OAuth2
 * @author Partydragen
 * @version 2.0.2
 * @license MIT
 */
class ApplicationIntegration extends IntegrationBase {

    protected Language $_language;
    protected Application $_application;

    public function __construct(Language $language, Application $application) {
        $this->_name = $application->getName();
        $this->_icon = 'fa-solid fa-globe';
        $this->_language = $language;
        $this->_application = $application;

        parent::__construct();
    }

    public function onLinkRequest(User $user) {
        Session::put('oauth_method', 'link_integration');
        
        $providers = NamelessOAuth::getInstance()->getProvidersAvailable();
        $provider = $providers[strtolower($this->_application->getName())];
        if ($provider == null) {
            Session::flash('connections_error', $this->_language->get('general', 'oauth_failed_setup'));
            return;
        }

        Redirect::to($provider['url']);
    }

    public function onVerifyRequest(User $user) {
        // Nothing to do here
    }

    public function onUnlinkRequest(User $user) {
        $integrationUser = new IntegrationUser($this, $user->data()->id, 'user_id');
        $integrationUser->unlinkIntegration();

        Session::flash('connections_success', $this->_language->get('user', 'integration_unlinked', ['integration' => Output::getClean($this->_name)]));

        // unlink integration on the other NamelessMC website
        if (!$this->_application->data()->nameless || $this->_application->data()->nameless_url == null || $this->_application->data()->nameless_api_key == null) {
            return;
        }

        $api_url = $this->_application->getWebsiteURL() . '/index.php?route=/api/v2';

        $header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->_application->data()->nameless_api_key
            ]
        ];

        $request = HttpClient::get($api_url . '/oauth2/application&client_id=' . $this->_application->data()->nameless_client_id, $header);
        if (!$request->hasError()) {
            $result = $request->json(true);
            
            if  ($result['nameless_integration']['enabled']) {
                HttpClient::post($api_url . '/users/' . $integrationUser->data()->identifier . '/integrations/unlink', json_encode([
                    'integration' => $result['name']
                ]), $header);
            }
        }
    }

    public function onSuccessfulVerification(IntegrationUser $integrationUser) {
        // Nothing to do here
    }

    public function validateUsername(string $username, int $integration_user_id = 0): bool {
        $validation = Validate::check(['username' => $username], [
            'username' => [
                Validate::REQUIRED => true,
            ]
        ])->messages([
            'username' => [
                Validate::REQUIRED => $this->_language->get('admin', 'integration_username_required', ['integration' => $this->getName()])
            ]
        ]);

        return $validation->passed();
    }

    public function validateIdentifier(string $identifier, int $integration_user_id = 0): bool {
        $validation = Validate::check(['identifier' => $identifier], [
            'identifier' => [
                Validate::REQUIRED => true,
                Validate::NUMERIC => true
            ]
        ])->messages([
            'identifier' => [
                Validate::REQUIRED => $this->_language->get('admin', 'integration_identifier_required', ['integration' => $this->getName()]),
                Validate::NUMERIC => $this->_language->get('admin', 'integration_identifier_invalid', ['integration' => $this->getName()])
            ]
        ]);

        if (count($validation->errors())) {
            // Validation errors
            foreach ($validation->errors() as $error) {
                $this->addError($error);
            }
        } else {
            // Ensure identifier doesn't already exist
            $exists = DB::getInstance()->query("SELECT * FROM nl2_users_integrations WHERE integration_id = ? AND identifier = ? AND id <> ?", [$this->data()->id, $identifier, $integration_user_id]);
            if ($exists->count()) {
                $this->addError($this->_language->get('user', 'integration_identifier_already_linked', ['integration' => $this->getName()]));
                return false;
            }
        }

        return $validation->passed();
    }

    public function allowLinking(): bool {
        return true;
    }

    public function onRegistrationPageLoad(Fields $fields) {
        // Nothing to do here
    }

    public function beforeRegistrationValidation(Validate $validate) {
        // Nothing to do here
    }

    public function afterRegistrationValidation() {
        // Nothing to do here
    }

    public function successfulRegistration(User $user) {
        // Link integration from oauth
        if (Session::exists('oauth_register_data')) {
            $data = json_decode(Session::get('oauth_register_data'), true);
            if ($data['provider'] == strtolower($this->_application->getName()) && isset($data['data']['id']) && isset($data['data']['username'])) {

                $id = $data['data']['id'];
                $username = $data['data']['username'];
                if ($this->validateIdentifier($id) && $this->validateUsername($username)) {
                    $integrationUser = new IntegrationUser($this);
                    $integrationUser->linkIntegration($user, $id, $username, true);
                    $integrationUser->verifyIntegration();
                    
                    $this->linkIntegration($user, $id);
                    $this->updateGroups($user, $id);
                }
            }

            Session::flash('connections_success', $this->_language->get('user', 'integration_linked', ['integration' => Output::getClean($this->_name)]));
        }
    }

    public function syncIntegrationUser(IntegrationUser $integration_user): bool {
        $this->updateGroups($integration_user->getUser(), $integration_user->data()->identifier);

        return true;
    }
    
    private function updateGroups(User $user, $identifier) {
        /*$groups = [];
        foreach ($user->getAllGroupIds() as $group) {
            $groups[] = $group;
        }

        $params = json_encode([
            'user' => $identifier,
            'groups' => $groups
        ]);

		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_application->data()->api_url . '/oauth2/sync-integration');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $this->_application->data()->api_key,
			'User-Agent: Partydragen, version 2.0.2, platform ' . php_uname('s') . '-' . php_uname( 'r' )
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $ch_result = curl_exec($ch);

        curl_close($ch);*/
    }
    
    private function linkIntegration(User $user, $identifier) {
        // Link integration on the other NamelessMC website
        if (!$this->_application->data()->nameless || $this->_application->data()->nameless_url == null || $this->_application->data()->nameless_api_key == null) {
            return;
        }

        $api_url = $this->_application->getWebsiteURL() . '/index.php?route=/api/v2';

        $header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->_application->data()->nameless_api_key
            ]
        ];

        $request = HttpClient::get($api_url . '/oauth2/application&client_id=' . $this->_application->data()->nameless_client_id, $header);
        if (!$request->hasError()) {
            $result = $request->json(true);
            
            if  ($result['nameless_integration']['enabled']) {
                HttpClient::post($api_url . '/users/' . $identifier . '/integrations/link', json_encode([
                    'integration' => $result['name'],
                    'identifier' => $user->data()->id,
                    'username' => $user->data()->username,
                    'verified' => true
                ]), $header);
            }
        }
    }
}