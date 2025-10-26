<?php

namespace Google_Docs_Integration\Libraries;

class Google_Docs_Integration {

    private $Google_Docs_Integration_settings_model;

    public function __construct() {
        $this->Google_Docs_Integration_settings_model = new \Google_Docs_Integration\Models\Google_Docs_Integration_settings_model();

        //load resources
        require_once(PLUGINPATH . "Google_Docs_Integration/ThirdParty/google-api-php-client-2-16-0/vendor/autoload.php");
    }

    //authorize connection
    public function authorize() {
        $client = $this->_get_client_credentials();
        $this->_check_access_token($client, true);
    }

    public function save_document($data, $id) {
        $service = $this->_get_google_docs_service();
        $document_data = array();

        if ($id) {
            $Google_Docs_model = new \Google_Docs_Integration\Models\Google_Docs_model();
            $google_document_id = $Google_Docs_model->get_one($id)->google_document_id;
            $documentId = $this->_updateDocument($google_document_id, $data);
        } else {
            $documentId = $this->_createDocument($service, $data);
        }

        if ($documentId) {
            $document_data["google_document_id"] = $documentId;
        }
        return $document_data;
    }

    private function _updateDocument($documentId, $data) {
        // Create a request to update the title
        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => get_array_value($data, "title")
        ]);

        // Execute the update
        $driveService = $this->_get_google_drive_service();
        $driveService->files->update($documentId, $fileMetadata);
        return $documentId;
    }

    // Function to create a new document
    private function _createDocument($service, $data) {
        $document = new \Google_Service_Docs_Document([
            'title' => get_array_value($data, "title")
        ]);

        $document = $service->documents->create($document, ['fields' => 'documentId']);
        $this->_make_document_public($document->documentId);

        return $document->documentId;
    }

    private function _get_google_drive_service() {
        $client = $this->_get_client_credentials();
        $this->_check_access_token($client);

        return new \Google_Service_Drive($client);
    }

    private function _make_document_public($documentId) {
        // Set the visibility of the document to public
        $driveService = $this->_get_google_drive_service();
        $newPermission = new \Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'writer'
        ]);

        $driveService->permissions->create($documentId, $newPermission);
    }

    //check access token
    private function _check_access_token($client, $redirect_to_settings = false) {
        //load previously authorized token from database, if it exists.
        $accessToken = get_google_docs_integration_setting("google_docs_oauth_access_token");
        if (get_google_docs_integration_setting("google_docs_authorized") && $accessToken && !$redirect_to_settings) {
            $client->setAccessToken(json_decode($accessToken, true));
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                if ($redirect_to_settings) {
                    app_redirect("settings/integration/google_docs");
                }
            } else {
                $authUrl = $client->createAuthUrl();
                app_redirect($authUrl, true);
            }
        } else {
            if ($redirect_to_settings) {
                app_redirect("settings/integration/google_docs");
            }
        }
    }

    //fetch access token with auth code and save to database
    public function save_access_token($auth_code) {
        $client = $this->_get_client_credentials();

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($auth_code);

        $error = get_array_value($accessToken, "error");

        if ($error)
            die($error);


        $client->setAccessToken($accessToken);

        // Save the token to database
        $new_access_token = json_encode($client->getAccessToken());

        if ($new_access_token) {
            $this->Google_Docs_Integration_settings_model->save_setting("google_docs_oauth_access_token", $new_access_token);

            //got the valid access token. store to setting that it's authorized
            $this->Google_Docs_Integration_settings_model->save_setting("google_docs_authorized", "1");
        }
    }
    //get service
    private function _get_google_docs_service() {
        $client = $this->_get_client_credentials();
        $this->_check_access_token($client);

        return new \Google_Service_Docs($client);
    }

    //delete file
    public function delete_document($document_id) {
        $driveService = $this->_get_google_drive_service();
        $driveService->files->delete($document_id);
    }

    //get client credentials
    private function _get_client_credentials() {
        $url = get_uri("google_docs_integration_settings/save_access_token");

        $client = new \Google_Client();
        $client->setApplicationName(get_setting('app_title'));
        $client->setRedirectUri($url);
        $client->setClientId(get_google_docs_integration_setting('google_docs_client_id'));
        $client->setClientSecret(get_google_docs_integration_setting('google_docs_client_secret'));
        $client->setScopes(array(
            \Google_Service_Docs::DOCUMENTS,
            \Google_Service_Drive::DRIVE
        ));
        $client->setAccessType("offline");
        $client->setPrompt('select_account consent');

        return $client;
    }
}
