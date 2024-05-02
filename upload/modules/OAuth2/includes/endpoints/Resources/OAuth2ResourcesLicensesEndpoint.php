<?php
class OAuth2ResourcesLicensesEndpoint extends AccessTokenAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/resources/licenses';
        $this->_module = 'OAuth2';
        $this->_description = 'Get user resource licenses';
        $this->_method = 'GET';
    }

    public function execute(Nameless2API $api, AccessToken $token): void {
        if (!$token->hasScope('resources.licenses')) {
            $api->throwError(OAuth2ApiErrors::ERROR_MISSING_SCOPE);
        }

        // Make sure user still exist
        $user = $token->user();
        if (!$user->exists()) {
            $api->throwError(Nameless2API::ERROR_CANNOT_FIND_USER);
        }

        // Get user resource licenses
        $resources_list = [];
        $resources = DB::getInstance()->query('SELECT nl2_resources.id as id, nl2_resources.name as name FROM nl2_resources_payments LEFT JOIN nl2_resources ON nl2_resources.id = nl2_resources_payments.resource_id WHERE nl2_resources_payments.status = 1 AND nl2_resources_payments.user_id = ?', [$token->user()->data()->id]);
        foreach ($resources->results() as $resource) {
            $resources_list[] = [
                'id' => $resource->id,
                'name' => $resource->name
            ];
        }

        $api->returnArray([
            'resources' => $resources_list
        ]);
    }
}