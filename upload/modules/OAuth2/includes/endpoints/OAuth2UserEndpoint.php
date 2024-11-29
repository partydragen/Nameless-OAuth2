<?php
class OAuth2UserEndpoint extends AccessTokenAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/user';
        $this->_module = 'OAuth2';
        $this->_description = 'Get user info from oauth2 access token';
        $this->_method = 'GET';
    }

    public function execute(Nameless2API $api, AccessToken $token): void {
        if (!$token->hasScope('identify')) {
            $api->throwError(OAuth2ApiErrors::ERROR_MISSING_SCOPE, ['scope' => 'identify']);
        }

        // Make sure user still exist
        $user = $token->user();
        if (!$user->exists()) {
            $api->throwError(Nameless2API::ERROR_CANNOT_FIND_USER);
        }

        $data = [
            'id' => $user->data()->id,
            'username' => $user->data()->username,
            'avatar' => $user->getAvatar(128, true)
        ];

        if ($token->hasScope('email')) {
            $data['email'] = $user->data()->email;
        }

        $api->returnArray($data);
    }
}