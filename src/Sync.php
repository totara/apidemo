<?php

namespace Totara\Apidemo;

use Symfony\Component\HttpClient\HttpClient;

class Sync {
    /** @var SyncRequest */
    private $sourceRequests;
    public function __construct() {
        require_once(__DIR__ . '/../config.php');

        $client = HttpClient::create();

        $sourceClient = new TotaraOauth2Client(
            $client,
            $source_site_url,
            $source_client_id,
            $source_client_secret
        );
        $this->sourceRequests = new SyncRequest($sourceClient);

        $targetClient = new TotaraOauth2Client(
            $client,
            $target_site_url,
            $target_client_id,
            $target_client_secret
        );

        $this->targetRequests = new SyncRequest($targetClient);
    }

    public function run() {
        echo "Running...\n";

        $sourceUsers = $this->sourceRequests->getUsers();
        $targetUsers = $this->targetRequests->getUsers();

        list($toCreate, $toUpdate, $toDelete) = $this->compareUsers($sourceUsers, $targetUsers);

        echo "Deleting users in target:\n";
        foreach ($toDelete as $deleteUser) {
            $this->deleteUserFromSourceData($deleteUser);
        }

        echo "Updating users in target:\n";
        foreach ($toUpdate as $updateUser) {
            $this->updateUserFromSourceData($updateUser);
        }

        echo "Creating users in target:\n";
        foreach ($toCreate as $createUser) {
            $this->createUserFromSourceData($createUser);
        }

        echo "Done\n";
    }

    private function compareUser($sourceUser, $targetUser) {
        $fieldsToUpdate = [];

        foreach ($sourceUser as $field => $value) {
            if ($field == 'id') {
                continue;
            }
            if (isset($targetUser[$field]) &&
                $value != $targetUser[$field]) {
                $fieldsToUpdate[$field] = $value;
            }
        }
        return $fieldsToUpdate;
    }

    private function compareUsers($sourceUsers, $targetUsers) {
        $toCreate = $toUpdate = $toDelete = [];

        foreach ($sourceUsers as $sourceUser) {
            $sourceFoundInTarget = false;
            foreach ($targetUsers as $targetUser) {
                if ($sourceUser['username'] == $targetUser['username']) {
                    $diff = $this->compareUser($sourceUser, $targetUser);
                    if (!empty($diff)) {
                        $diff['username'] = $sourceUser['username'];
                        $toUpdate[] = $diff;
                    }
                    $sourceFoundInTarget = true;
                    break;
                }
            }
            if ($sourceFoundInTarget == false) {
                $toCreate[] = $sourceUser;
            }
        }

        foreach ($targetUsers as $targetUser) {
            $targetFoundInSource = false;
            foreach ($sourceUsers as $sourceUser) {
                if ($sourceUser['username'] == $targetUser['username']) {
                    $targetFoundInSource = true;
                    break;
                }
            }
            if ($targetFoundInSource == false) {
                $toDelete[] = $targetUser;
            }
        }

        return [$toCreate, $toUpdate, $toDelete];
    }

    private function createUserFromSourceData($user) {
        // Map data to required format.
        $input = [
            'username' => $user['username'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'email' => $user['email'],
            'suspended' => $user['suspended'],
            'auth' => 'manual',
            'password' => 'changeme!',
        ];
        $apiResponse = $this->targetRequests->createUser($input);
        if ($apiResponse->isError()) {
            echo "ERROR: " . $apiResponse->getErrorsAsString();
        } else {
            $data = $apiResponse->getResponseData('core_user_create_user');
            if (isset($data['user']['id'])) {
                echo "Created user " . $data['user']['id'] . " (" . $user['username'] . ")\n";
            } else {
                echo "Create user: no errors but malformed response:\n";
                var_dump($apiResponse->getResponseData());
            }
        }
    }

    private function updateUserFromSourceData($user) {
        $targetUser = [
            'username' => $user['username'],
        ];
        $input = $user;
        unset($input['username']);
        $apiResponse = $this->targetRequests->updateUser($targetUser, $input);

        if ($apiResponse->isError()) {
            echo "ERROR: " . $apiResponse->getErrorsAsString();
        } else {
            $data = $apiResponse->getResponseData('core_user_update_user');
            if (isset($data['user']['id'])) {
                echo "Updated user " . $data['user']['id'] . " (" . $user['username'] . ")\n";
            } else {
                echo "Update user: no errors but malformed response:\n";
                var_dump($apiResponse->getResponseData());
            }
        }
    }

    private function deleteUserFromSourceData($user) {
        $targetUser = [
            'username' => $user['username'],
        ];

        $apiResponse = $this->targetRequests->deleteUser($targetUser);

        if ($apiResponse->isError()) {
            echo "ERROR: " . $apiResponse->getErrorsAsString();
        } else {
            $data = $apiResponse->getResponseData('core_user_delete_user');
            if (isset($data['user_id'])) {
                echo "Deleted user " . $data['user_id'] . " (" . $user['username'] . ")\n";
            } else {
                echo "Delete user: no errors but malformed response:\n";
                var_dump($apiResponse->getResponseData());
            }
        }
    }
}