<?php
class Connection {
    public $is_connected;
    public $client;
    
    public function __construct() {
        $this->credentials = "loginGrapesCred.json";
        $this->client = $this->create_client();
    }

    public function get_client() {
        return $this->client;
    }
    
    public function get_credentials() {
        return $this->credentials;
    }

    public function is_connected() {
        return $this->is_connected;
    }

    public function get_unauthenticated_data() {
        // Request authorization from the user.
        $authUrl = $this->client->createAuthUrl();
        return $authUrl;
        // return $this->client->createAuthUrl();
    }
    
    public function credentials_in_browser() {
        if(isset($_GET['code'])) {
            return true;
        }
        return false;
    }

    public function create_client() {
        $client = new Google_Client();
        $client->setApplicationName('Google Drive API Quickstart');
        $client->setScopes(Google_Service_Drive::DRIVE);
        $client->setAuthConfig('loginGrapesCred.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } elseif ($this->credentials_in_browser()) {
                $authCode = $_GET['code'];
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            } else {
                $this->is_connected = false;
                return $client;
            }
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        } else {
            echo "<p>Token no expirado</p>";
        }
        $this->is_connected = true;
        return $client;
    }
}