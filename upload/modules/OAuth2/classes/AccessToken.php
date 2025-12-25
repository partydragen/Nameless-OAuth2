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

            if ($field === 'access_token') {
                if (hash_equals($value, $this->_data->access_token) && $this->_data->expires >= time()) {
                    $this->_authorised = true;

                    $this->update([
                        'last_used' => date('U')
                    ]);
                }
            }
        }
    }

    /**
     * Update token data in the database.
     *
     * @param array $fields Column names and values to update.
     */
    public function update(array $fields = []) {
        if (!DB::getInstance()->update('oauth2_tokens', $this->data()->id, $fields)) {
            throw new Exception('There was a problem updating the token!');
        }
    }

    public function exists(): bool {
        return (!empty($this->_data));
    }

    public function isAuthorised(): bool {
        return $this->_authorised;
    }

    public function hasScope(string $scope): bool {
        $scopes = explode(' ', $this->data()->scopes);

        return in_array($scope, $scopes);
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