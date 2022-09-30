<?php
class OAuth2UserEndpoint extends AccessTokenAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/user';
        $this->_module = 'OAuth2';
        $this->_description = 'Get user info from oauth2 access token';
        $this->_method = 'GET';
    }

    public function execute(Nameless2API $api): void {
        $auth_header = HttpUtils::getHeader('Authorization');
        $exploded = explode(' ', trim($auth_header));

        // Get data from access token
        $access_token = DB::getInstance()->get('oauth2_tokens', ['access_token', $exploded[1]]);
        if (!$access_token->count()) {
            $api->throwError(OAuth2ApiErrors::ERROR_NOT_AUTHORIZED);
        }
        $access_token = $access_token->first();

        // Make sure user still exist
        $user = new User($access_token->user_id);
        if (!$user->exists()) {
            $api->throwError(Nameless2API::ERROR_CANNOT_FIND_USER);
        }

        $api->returnArray([
            'id' => $user->data()->id,
            'username' => $user->data()->username,
            'email' => $user->data()->email
        ]);
    }
}