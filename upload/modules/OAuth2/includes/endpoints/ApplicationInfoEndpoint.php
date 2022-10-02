<?php
class ApplicationInfoEndpoint extends KeyAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/application';
        $this->_module = 'OAuth2';
        $this->_description = 'Get OAuth2 application info';
        $this->_method = 'GET';
    }

    public function execute(Nameless2API $api): void {
        $api->validateParams($_GET, ['client_id']);

        $application = new Application($_GET['client_id'], 'client_id');
        if (!$application->exists()) {
            $api->throwError('oauth2:cannot_find_application');
        }

        $api->returnArray([
            'name' => $application->data()->name,
            'redirect_uri' => $application->data()->redirect_uri,
            'nameless_integration' => [
                'enabled' => (bool) $application->data()->nameless,
                'client_id' => $application->data()->nameless_client_id,
                'api_key' => $application->data()->nameless_api_key
            ]
        ]);
    }
}