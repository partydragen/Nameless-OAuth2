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
        
        $token = DB::getInstance()->get('oauth2_tokens', ['code', $output['code']]);
        if (!$token->count()) {
            $api->throwError('error', $output);
        }
        $token = $token->first();

        $api->returnArray(['access_token' => $token->access_token, 'refresh_token' => $token->refresh_token]);
    }
}