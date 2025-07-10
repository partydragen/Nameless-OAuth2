<?php
class OAuth2StoreCustomerEndpoint extends AccessTokenAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/store/balance';
        $this->_module = 'OAuth2';
        $this->_description = 'Get user store balance';
        $this->_method = 'GET';
    }

    public function execute(Nameless2API $api, AccessToken $token): void {
        if (!$token->hasScope('store.balance')) {
            $api->throwError(OAuth2ApiErrors::ERROR_MISSING_SCOPE, ['scope' => 'store.balance']);
        }

        // Get user resource licenses
        $customer = new Customer($token->user());

        $api->returnArray([
            'cents' => (int) $customer->data()->cents,
            'credits' => (double) $customer->data()->cents / 100
        ]);
    }
}