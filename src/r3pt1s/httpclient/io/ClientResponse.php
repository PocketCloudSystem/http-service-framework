<?php

namespace r3pt1s\httpclient\io;

use r3pt1s\httpserver\util\RequestMethod;
use r3pt1s\httpserver\util\StatusCode;

final readonly class ClientResponse {

    public function __construct(
        private string $url,
        private RequestMethod $requestMethod,
        private StatusCode $statusCode,
        private array $headers,
        private string $body,
        private mixed $parsedBody,
        private int $takenRetries
    ) {}

    public function isInformational(): bool {
        return $this->statusCode->isInformational();
    }

    public function isSuccess(): bool {
        return $this->statusCode->isSuccess();
    }

    public function isRedirection(): bool {
        return $this->statusCode->isRedirection();
    }

    public function isClientError(): bool {
        return $this->statusCode->isClientError();
    }

    public function isServerError(): bool {
        return $this->statusCode->isServerError();
    }

    public function url(): string {
        return $this->url;
    }

    public function requestMethod(): RequestMethod {
        return $this->requestMethod;
    }

    public function statusCode(): StatusCode {
        return $this->statusCode;
    }

    public function getHeader(string $key, mixed $default = null): mixed {
        return $this->headers[$key] ?? $default;
    }

    public function headers(): array {
        return $this->headers;
    }

    public function bodyRaw(): string {
        return $this->body;
    }

    public function body(): mixed {
        return $this->parsedBody ?? $this->body;
    }

    public function takenRetries(): int {
        return $this->takenRetries;
    }
}