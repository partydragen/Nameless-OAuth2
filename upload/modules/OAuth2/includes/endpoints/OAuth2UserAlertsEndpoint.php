<?php
class OAuth2UserAlertsEndpoint extends AccessTokenAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/user/alerts';
        $this->_module = 'OAuth2';
        $this->_description = 'Get user alerts';
        $this->_method = 'GET';
    }

    public function execute(Nameless2API $api, AccessToken $token): void {
        if (!$token->hasScope('user.alerts')) {
            $api->throwError(OAuth2ApiErrors::ERROR_MISSING_SCOPE, ['scope' => 'user.alerts']);
        }

        $api->returnArray(['alerts' => Alert::getAlerts($token->user()->data()->id)]);
    }
}