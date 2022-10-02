<?php
/**
 * OAuth2 group sync injector implementation.
 *
 * @package Modules\OAuth2
 * @author Partydragen
 * @version 2.0.2
 * @license MIT
 */
class ApplicationGroupSyncInjector implements GroupSyncInjector {
    protected Application $_application;

    public function __construct(Application $application) {
        $this->_application = $application;
    }

    public function getModule(): string {
        return 'OAuth2';
    }

    public function getName(): string {
        return $this->_application->getName();
    }

    public function getColumnName(): string {
        return strtolower($this->_application->getName() . '_group_id');
    }

    public function getColumnType(): string {
        return 'INT';
    }

    public function shouldEnable(): bool {
        return true;
    }

    public function getNotEnabledMessage(Language $language): string {
        return $this->_application->getName() . ' module not configurated';
    }

    public function getSelectionOptions(): array {
        $groups = [];

        if ($this->_application->data()->nameless_url == null || $this->_application->data()->nameless_api_key == null) {
            return $groups;
        }

        $header = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->_application->data()->nameless_api_key
            ]
        ];

        $request = HttpClient::get($this->_application->getWebsiteURL() . '/index.php?route=/api/v2/groups', $header);
        if (!$request->hasError()) {
            $result = $request->json(true);

            foreach ($result['groups'] as $group) {
                $groups[] = [
                    'id' => Output::getClean($group['id']),
                    'name' => Output::getClean($group['name'])
                ];
            }
        }

        return $groups;
    }

    public function getValidationRules(): array {
        return [
            Validate::MIN => 1,
            Validate::MAX => 11,
            Validate::NUMERIC => true
        ];
    }

    public function getValidationMessages(Language $language): array {
        return [
            Validate::MIN => 'Invalid group id',
            Validate::MAX => 'Invalid group id',
            Validate::NUMERIC => 'Invalid group id',
        ];
    }

    public function addGroup(User $user, $group_id): bool {
        return Discord::updateDiscordRoles($user, [$group_id], []) === true;
    }

    public function removeGroup(User $user, $group_id): bool {
        return Discord::updateDiscordRoles($user, [], [$group_id]) === true;
    }
}
