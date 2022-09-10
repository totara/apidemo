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

        echo "TO CREATE:\n";
        var_dump($toCreate);

        echo "TO UPDATE:\n";
        var_dump($toUpdate);

        echo "TO DELETE:\n";
        var_dump($toDelete);
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
                    $diff['username'] = $sourceUser['username'];
                    if (!empty($diff)) {
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
}