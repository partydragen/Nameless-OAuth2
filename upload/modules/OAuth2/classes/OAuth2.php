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

    public static function syncUserApplications(User $user) {
        $integrations = $user->getIntegrations();
        foreach ($integrations as $integration_user) {
            $integration = $integration_user->getIntegration();

            if ($integration instanceof ApplicationIntegration) {
                $integration->syncExternalUserIntegration($user);
            }
        }
    }
}