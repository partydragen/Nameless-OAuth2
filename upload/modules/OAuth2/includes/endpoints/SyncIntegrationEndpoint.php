<?php
class SyncIntegrationEndpoint extends KeyAuthEndpoint {

    public function __construct() {
        $this->_route = 'oauth2/sync-integration';
        $this->_module = 'OAuth2';
        $this->_description = 'Sync OAuth2 Integration';
        $this->_method = 'POST';
    }

    public function execute(Nameless2API $api): void {
        $api->validateParams($_POST, ['user']);

        $user = $api->getUser('id', $_POST['user']);

        $log_array = GroupSyncManager::getInstance()->broadcastChange(
            $user,
            ApplicationGroupSyncInjector::class,
            $_POST['groups'] ?? []
        );

        if (count($log_array)) {
            Log::getInstance()->log('oauth2/group_set', json_encode($log_array), $user->data()->id);
        }

        $api->returnArray(array_merge(['message' => 'Success'], $log_array));
    }
}