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

        if (!isset($output['client_id'])) {
            $api->throwError('oauth2:invalid_request', 'Missing client_id');
        }

        if (!isset($output['client_secret'])) {
            $api->throwError('oauth2:invalid_request', 'Missing client_secret');
        }

        if (!isset($output['grant_type'])) {
            $api->throwError('oauth2:invalid_request', 'Missing grant_type');
        }

        // Get application by client id
        $application = new Application($output['client_id'], 'client_id');
        if (!$application->exists()) {
            $api->throwError('oauth2:invalid_credentials');
        }

        // Validate client secret
        if (!hash_equals($output['client_secret'], $application->data()->client_secret)) {
            $api->throwError('oauth2:invalid_credentials');
        }

        switch ($output['grant_type']) {
            case 'authorization_code':
                if (!isset($output['code'])) {
                    $api->throwError('oauth2:invalid_request', 'Missing code');
                }

                // Get token by code
                $token = new AccessToken($output['code'], 'code');
                if (!$token->exists()) {
                    $api->throwError('oauth2:invalid_code');
                }

                if ($token->data()->application_id != $application->data()->id) {
                    $api->throwError('oauth2:invalid_grant');
                }

                $stored_challenge = $token->data()->code_challenge;
                $stored_method = $token->data()->code_challenge_method ?? 'plain';
                if ($stored_challenge) {
                    // PKCE was used → require code_verifier
                    if (!isset($output['code_verifier'])) {
                        $api->throwError('oauth2:invalid_request', 'Missing code_verifier');
                    }

                    $verifier = $output['code_verifier'];

                    // Compute expected challenge
                    if ($stored_method === 'S256') {
                        $computed_challenge = base64_url_encode(hash('sha256', $verifier, true));
                    } else {
                        // 'plain' method
                        $computed_challenge = $verifier;
                    }

                    if (!hash_equals($computed_challenge, $stored_challenge)) {
                        $api->throwError('oauth2:invalid_grant', 'Invalid code_verifier');
                    }
                }

                $token->update([
                    'code' => null,
                    'expires' => strtotime('+3600 seconds'),
                ]);

                $api->returnArray([
                    'access_token' => $token->data()->access_token,
                    'refresh_token' => $token->data()->refresh_token,
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                    'scope' => $token->data()->scopes ?? ''
                ]);
                break;

            case 'refresh_token':
                if (!isset($output['refresh_token'])) {
                    $api->throwError('oauth2:invalid_request', 'Missing refresh_token');
                }

                // Get token by access token
                $token = new AccessToken($output['refresh_token'], 'refresh_token');
                if (!$token->exists()) {
                    $api->throwError('oauth2:invalid_grant');
                }

                if ($token->data()->application_id != $application->data()->id) {
                    $api->throwError('oauth2:invalid_grant');
                }

                // Generate new access token and refresh token
                $new_access = SecureRandom::alphanumeric();
                $new_refresh = SecureRandom::alphanumeric();
                $token->update([
                    'access_token' => $new_access,
                    'refresh_token' => $new_refresh,
                    'expires' => strtotime('+3600 seconds'),
                ]);

                $api->returnArray([
                    'access_token' => $new_access,
                    'refresh_token' => $new_refresh,
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                    'scope' => $token->data()->scopes ?? ''
                ]);
                break;

            default:
                $api->throwError('oauth2:unsupported_grant_type');
        }
    }
}

function base64_url_encode($data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}