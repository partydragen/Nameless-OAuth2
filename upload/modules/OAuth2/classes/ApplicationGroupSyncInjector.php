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
        return strtolower($this->_application->getName() . '_tier_id');
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

        $groups[] = [
            'id' => 5,
            'name' => 'Tier-1'
        ];
        $groups[] = [
            'id' => 6,
            'name' => 'Tier-2'
        ];
        $groups[] = [
            'id' => 7,
            'name' => 'Tier-3'
        ];
        $groups[] = [
            'id' => 8,
            'name' => 'Tier-4'
        ];
        $groups[] = [
            'id' => 11,
            'name' => 'Tier-5'
        ];
        $groups[] = [
            'id' => 12,
            'name' => 'Tier-6'
        ];

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
            Validate::MIN => 'Invalid patreon tier id',
            Validate::MAX => 'Invalid patreon tier id',
            Validate::NUMERIC => 'Invalid patreon tier id',
        ];
    }

    public function addGroup(User $user, $group_id): bool {
        return Discord::updateDiscordRoles($user, [$group_id], []) === true;
    }

    public function removeGroup(User $user, $group_id): bool {
        return Discord::updateDiscordRoles($user, [], [$group_id]) === true;
    }
}
