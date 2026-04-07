<?php

namespace r3pt1s\httpserver\io;

use r3pt1s\httpserver\util\StatusCode;
use r3pt1s\httpserver\util\Utils;

final readonly class Response {

	public function __construct(
        private int $statusCode,
        private string $body,
        private ?string $customMessage,
        private array $headers
    ) {}

    public function buildResponseString(): string {
        $httpResponse = "HTTP/1.1 " . $this->statusCode . " " . (StatusCode::toString($this->statusCode) ?? $this->customMessage) . "\r\n";
        $httpResponse .=  implode("\r\n", Utils::encodeHeaders($this->headers)) . "\r\n";
        $httpResponse .= "\r\n";
        $httpResponse .= $this->body;
        return $httpResponse;
    }

    public function body(): string {
        return $this->body;
    }

    public function customMessage(): ?string {
        return $this->customMessage;
    }

    public function header(string $key, mixed $default = null): mixed {
        return $this->headers[$key] ?? $default;
    }

    public function getHeader(string $key, mixed $default = null): mixed {
        return $this->headers[$key] ?? $default;
    }

    public function headers(): array {
        return $this->headers;
    }

    public function statusCode(): int {
        return $this->statusCode;
    }
}