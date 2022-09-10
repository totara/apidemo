<?php

namespace Totara\Apidemo;

class ApiResponse {
    private $response;

    public function __construct($response) {
        $this->response = $response;
    }

    public function isError(): bool {
        return !empty($this->response['errors']);
    }

    public function getResponseData($request_name = null): array {
        if (is_null($request_name)) {
            return $this->response['data'] ?? [];
        }
        return $this->response['data'][$request_name] ?? [];
    }

    public function getErrorsAsArray() {
        return $this->response['errors'] ?? [];
    }

    public function getErrorsAsString() {
        $errors = $this->getErrorsAsArray();
        $out = count($errors) . " errors found: ";
        foreach ($errors as $error) {
            $errorString = $error['debugMessage'] ?? $error['message'] ?? "Unknown error";
            $out .= " {$errorString}\n";
        }
        return $out;
    }
}