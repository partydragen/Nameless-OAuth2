<?php
/**
 * Allows an endpoint to require an Access Token to be present (and valid) in the request.
 *
 * @package Modules\OAuth2
 * @author Partydragen
 * @version 2.0.2
 * @license MIT
 */
class AccessTokenAuthEndpoint extends EndpointBase {

    private AccessToken $_token;

    /**
     * Determine if the passed Access Token (in Authorization header) is valid.
     *
     * @param Nameless2API $api Instance of the Nameless2API class
     * @return bool Whether the Access Token is valid
     */
    final public function isAuthorised(Nameless2API $api): bool {
        $auth_header = HttpUtils::getHeader('Authorization');

        if ($auth_header === null) {
            $api->throwError(Nameless2API::ERROR_MISSING_API_KEY, 'Missing authorization header');
        }

        $exploded = explode(' ', trim($auth_header));
        if (count($exploded) !== 2 ||
            strcasecmp($exploded[0], 'Bearer') !== 0) {
            $api->throwError(Nameless2API::ERROR_MISSING_API_KEY, 'Authorization header not in expected format');
        }

        $this->_token = new AccessToken($exploded[1]);
        return $this->_token->isAuthorised();
    }

    public function customParams(): array {
        return [$this->_token];
    }
}
