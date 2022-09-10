<?php

namespace Totara\Apidemo;

use Totara\Apidemo\Exception\InvalidRequestException;

class SyncRequest {
    const REQUEST_MAX_ITEMS = 50;

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

    public function getAllPaginatedResults($query_name, $items_field, $query, $params = []) {
        $items = [];
        while (1) {
            $response = $this->client->makeAuthenticatedRequest($query, $params);
            $apiResponse = new ApiResponse($response);

            if ($apiResponse->isError()) {
                throw new InvalidRequestException("ERROR: ". $apiResponse->getErrorsAsString());
            }

            $query_data = $apiResponse->getResponseData($query_name);

            if (!isset($query_data[$items_field])) {
                throw new InvalidRequestException("ERROR: No '{$items_field}' property in response.");
            }

            if (empty($query_data[$items_field])) {
                // No more records, break from loop.
                break;
            }

            $items += $query_data[$items_field];

            $next_cursor = $query_data['next_cursor'] ?? null;

            if (empty($next_cursor)) {
                // No more records to fetch
                break;
            }

            $params['cursor'] = $next_cursor;

            unset($response);
            unset($query_data);
        }

        return $items;
    }

    public function getUsers() {
        $query = 'query get_users($pagination: core_pagination_input, $sort: [core_sort_input!]) {
            core_user_users(
                query: {
                    pagination: $pagination
                    sort: $sort
                }
            ) {
                items {
                    id
                    username
                    firstname
                    lastname
                    email
                    suspended
                }
                total
                next_cursor
            }
        }';
        $params = [
            'limit' => self::REQUEST_MAX_ITEMS,
            'cursor' => null,
        ];
        return $this->getAllPaginatedResults('core_user_users', 'items', $query, $params);
    }
}