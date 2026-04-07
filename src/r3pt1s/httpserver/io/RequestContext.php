<?php

namespace r3pt1s\httpserver\io;

use r3pt1s\httpserver\route\Path;
use r3pt1s\httpserver\util\Address;
use r3pt1s\httpserver\util\RequestMethod;

final readonly class RequestContext {

	public function __construct(
        private Address $address,
        private RequestMethod $method,
        private Path $path,
        private array $queries,
        private array $headers,
        private ?string $body,
        private mixed $parsedBody
    ) {}

    public function hasQuery(string $key): bool {
        return isset($this->queries[$key]);
    }

    public function hasHeader(string $key): bool {
        return isset($this->headers[$key]);
    }

    public function address(): Address {
        return $this->address;
    }

    public function method(): RequestMethod {
        return $this->method;
    }

    public function path(): Path {
        return $this->path;
    }

    public function getQuery(string $key, mixed $default = null): mixed {
        return $this->queries[$key] ?? $default;
    }

    public function queries(bool $sorted = false): array {
        $queries = $this->queries;
        if ($sorted) ksort($queries);
        return $queries;
    }

    public function getHeader(string $key, ?string $default = null): ?string {
        return $this->headers[$key] ?? $default;
    }

    public function headers(): array {
        return $this->headers;
    }

    public function bodyRaw(): ?string {
        return $this->body;
    }

    public function body(): mixed {
        return $this->parsedBody ?? $this->body;
    }

    public function parsedBody(): mixed {
        return $this->parsedBody;
    }
}