<?php

namespace Totara\Apidemo;

class SyncRequest {
    /** @var TotaraOauth2Client */
    private $client;

    public function __construct(TotaraOauth2Client $TotaraOauth2Client) {
        $this->client = $TotaraOauth2Client;
    }

    public function getStatus() {
        $query = '{
            totara_webapi_status {
                status
                timestamp
            }
        }';
        return $this->client->makeAuthenticatedRequest($query);
    }
}