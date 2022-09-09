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
    }

    public function run() {
        echo "Running...\n";

        var_dump($this->sourceRequests->getStatus());

    }
}