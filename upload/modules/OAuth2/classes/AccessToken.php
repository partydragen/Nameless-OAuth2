<?php
/**
 * AccessToken
 *
 * @package Modules\OAuth2
 * @author Partydragen
 * @version 2.2.0
 * @license MIT
 */
class AccessToken {

    private $_data;
    private bool $_authorised = false;
    private User $_user;

    public function __construct(string $value, string $field = 'access_token') {
        $token = DB::getInstance()->get('oauth2_tokens', [$field, $value]);
        if ($token->count()) {
            $this->_data = $token->first();

            if (hash_equals($value, $this->_data->access_token)) {
                $this->_authorised = true;
            }
        }
    }

    public function exists(): bool {
        return (!empty($this->_data));
    }

    public function isAuthorised(): bool {
        return $this->_authorised;
    }

    public function hasScope(string $scope): bool {
        return true;
    }

    /**
     * Get the NamelessMC User that belong to this access token.
     *
     * @return User NamelessMC User that belong to this access token
     */
    public function user(): User {
        return $this->_user ??= new User($this->data()->user_id);
    }

    /**
     * Get the access token data.
     *
     * @return object Get the access token data.
     */
    public function data(): object {
        return $this->_data;
    }
}