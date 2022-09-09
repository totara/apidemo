<?php

namespace Totara\Apidemo;

class SyncRequest {
    private $client;

    public function __contruct($TotaraOauth2Client) {
        $this->client = $TotaraOauth2Client;
    }
}