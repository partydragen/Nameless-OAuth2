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

        $user = $token->user();
        $data = [
            'id' => $user->data()->id,
            'username' => $user->data()->username,
            'avatar' => $user->getAvatar(128, true)
        ];

        if ($token->hasScope('email')) {
            $data['email'] = $user->data()->email;
        }

        if ($token->hasScope('user.groups')) {
            $groups_array = [];
            foreach ($user->getGroups() as $group) {
                $group_array = [
                    'id' => (int)$group->id,
                    'name' => $group->name,
                    'order' => (int)$group->order,
                ];

                $groups_array[] = $group_array;
            }
            $data['groups'] = $groups_array;
        }

        if ($token->hasScope('user.integrations')) {
            $integrations_array = [];
            foreach ($user->getIntegrations() as $key => $integrationUser) {
                if ($integrationUser->data()->identifier === null && $integrationUser->data()->username === null) {
                    continue;
                }

                $integrations_array[] = [
                    'integration' => Output::getClean($key),
                    'identifier' => Output::getClean($integrationUser->data()->identifier),
                    'username' => Output::getClean($integrationUser->data()->username),
                    'verified' => $integrationUser->isVerified(),
                    'linked_date' => $integrationUser->data()->date
                ];
            }
            $data['integrations'] = $integrations_array;
        }

        $api->returnArray($data);
    }
}