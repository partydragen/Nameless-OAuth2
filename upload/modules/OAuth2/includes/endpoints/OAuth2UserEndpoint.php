<?php
class OAuth2UserEndpoint extends AccessTokenAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/user';
        $this->_module = 'OAuth2';
        $this->_description = 'Get user info from oauth2 access token';
        $this->_method = 'GET';
    }

    public function execute(Nameless2API $api, AccessToken $token): void {
        if (!$token->hasScope('identity')) {
            $api->throwError(OAuth2ApiErrors::ERROR_MISSING_SCOPE);
        }

        // Make sure user still exist
        $user = $token->user();
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