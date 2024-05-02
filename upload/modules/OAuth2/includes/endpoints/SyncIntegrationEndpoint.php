<?php
class SyncIntegrationEndpoint extends KeyAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/user/sync-integration';
        $this->_module = 'OAuth2';
        $this->_description = 'Sync user OAuth2 Integration';
        $this->_method = 'POST';
    }

    public function execute(Nameless2API $api): void {
        $api->validateParams($_POST, ['user', 'external_application']);

        $application = new Application($_POST['external_application']['client_id'], 'client_id');
        if (!$application->exists()) {
            $api->throwError(OAuth2ApiErrors::ERROR_CANNOT_FIND_APPLICATION);
        }

        $integration = Integrations::getInstance()->getIntegration($application->getName());
        if ($integration == null) {
            $api->throwError(CoreApiErrors::ERROR_INVALID_INTEGRATION);
        }

        $integration_user = new IntegrationUser($integration, $_POST['user']['id'], 'identifier');
        if (!$integration_user->exists()) {
            $api->throwError(CoreApiErrors::ERROR_INTEGRATION_NOT_LINKED);
        }

        // Sync integration username
        if (isset($_POST['user']['username'])) {
            $integration_user->update([
                'username' => $_POST['user']['username']
            ]);
        }

        // Sync integration
        if (isset($_POST['user']['groups'])) {
            $user = $integration_user->getUser();

            $log_array = GroupSyncManager::getInstance()->broadcastChange(
                $user,
                ApplicationGroupSyncInjector::class,
                $_POST['user']['groups']
            );

            if (count($log_array)) {
                Log::getInstance()->log('oauth2/group_set', json_encode($log_array), $user->data()->id);
            }
        }

        $api->returnArray(array_merge(['message' => 'Success']));
    }
}