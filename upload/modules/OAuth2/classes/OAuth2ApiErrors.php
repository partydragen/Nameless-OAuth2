<?php
/**
 * Contains namespaced API error messages for the OAuth2 module.
 * These have no versioning, and are not meant to be used by any other modules.
 *
 * @package Modules\OAuth2
 * @author Partydragen
 * @version 2.0.2
 * @license MIT
 */
class OAuth2ApiErrors {
    public const ERROR_NOT_AUTHORIZED = 'oauth2:not_authorized';
    public const ERROR_MISSING_SCOPE = 'oauth2:missing_scope';
    public const ERROR_CANNOT_FIND_APPLICATION = 'oauth2:cannot_find_application';
}