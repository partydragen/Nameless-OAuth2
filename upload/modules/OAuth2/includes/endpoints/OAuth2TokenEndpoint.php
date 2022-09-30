<?php
class OAuth2TokenEndpoint extends NoAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/token';
        $this->_module = 'OAuth2';
        $this->_description = 'Get access token from OAuth2 code';
        $this->_method = 'POST';
    }

    public function execute(Nameless2API $api): void {
        $bodyReceived = file_get_contents('php://input');

        parse_str($bodyReceived, $output);

        // Get application by client id
        $application = new Application($output['client_id'], 'client_id');
        if (!$application->exists()) {
            $api->throwError('oauth2:invalid_credentials');
        }

        // Validate client secret
        if (!hash_equals($output['client_secret'], $application->data()->client_secret)) {
            $api->throwError('oauth2:invalid_credentials');
        }

        // Get tokens by code
        $token = DB::getInstance()->get('oauth2_tokens', [['application_id', $application->data()->id], ['code', $output['code']]]);
        if (!$token->count()) {
            $api->throwError('oauth2:invalid_code');
        }
        $token = $token->first();

        $api->returnArray(['access_token' => $token->access_token, 'refresh_token' => $token->refresh_token]);
    }
}