<?php
/**
 * The Application class for OAuth2.
 *
 * @package Modules\OAuth2
 * @author Partydragen
 * @version 2.0.2
 * @license MIT
 */
class Application {

    private DB $_db;

    /**
     * @var object|null The application data. Basically just the row from `nl2_oauth2_applications` where the application ID is the key.
     */
    private $_data;

    public function __construct(?string $value = null, ?string $field = 'id', $query_data = null) {
        $this->_db = DB::getInstance();

        if (!$query_data && $value) {
            $data = $this->_db->get('oauth2_applications', [$field, '=', $value]);
            if ($data->count()) {
                $this->_data = $data->first();
            }
        } else if ($query_data) {
            // Load data from existing query.
            $this->_data = $query_data;
        }
    }

    /**
     * Update a application data in the database.
     *
     * @param array $fields Column names and values to update.
     */
    public function update(array $fields = []) {
        if (!$this->_db->update('oauth2_applications', $this->data()->id, $fields)) {
            throw new Exception('There was a problem updating application');
        }
    }

    /**
     * Does this application exist?
     *
     * @return bool Whether the application exists (has data) or not.
     */
    public function exists(): bool {
        return (!empty($this->_data));
    }

    /**
     * Get the application data.
     *
     * @return object This application data.
     */
    public function data() {
        return $this->_data;
    }

    public function getName() {
        return $this->data()->name;
    }

    public function getWebsiteURL() {
        return $this->data()->nameless_url;
    }

    public function getRedirectURI() {
        return $this->data()->redirect_uri;
    }
}