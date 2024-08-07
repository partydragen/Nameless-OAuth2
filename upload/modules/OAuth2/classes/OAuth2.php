<?php
/**
 * OAuth2
 *
 * @package Modules\OAuth2
 * @author Partydragen
 * @version 2.2.0
 * @license MIT
 */
class OAuth2 {

    /**
     * @var array<string, string> All registered scopes.
     */
    private static array $_scopes;

    public static function syncUserApplications(User $user) {
        $integrations = $user->getIntegrations();
        foreach ($integrations as $integration_user) {
            $integration = $integration_user->getIntegration();

            if ($integration instanceof ApplicationIntegration) {
                $integration->syncExternalUserIntegration($user);
            }
        }
    }

    /**
     *  Register a scope.
     *
     * @param string $scope     OAuth scope.
     * @param string $title     Tile for what the scope do.
     */
    public static function registerScope(string $scope, string $title) {
        self::$_scopes[$scope] = $title;
    }

    /**
     * Get all registered scopes.
     *
     * @return array<string, string> Scope array.
     */
    public static function getScopes(): array {
        return self::$_scopes;
    }

    public static function getScope(string $scope): ?string {
        if (array_key_exists($scope, self::$_scopes)) {
            return self::$_scopes[$scope];
        }

        return null;
    }

    public static function getScopesFromString(string $scopes): array {
        $scopes_list = [];

        $requested_scopes = explode(' ', str_replace('+', ' ', $scopes));
        foreach ($requested_scopes as $scope) {
            if (array_key_exists($scope, self::$_scopes)) {
                $scopes_list[$scope] = self::$_scopes[$scope];
            }
        }

        return $scopes_list;
    }
}