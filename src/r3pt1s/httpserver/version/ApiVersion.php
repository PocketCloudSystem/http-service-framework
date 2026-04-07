<?php

namespace r3pt1s\httpserver\version;

use r3pt1s\httpserver\socket\auth\Authentication;

class ApiVersion {

    /**
     * @param string $version
     * @param Authentication $authentication
     * @param array $paths the array of paths (string)
     */
    public function __construct(
        private readonly string $version,
        private readonly Authentication $authentication,
        private array $paths = []
    ) {}

    public function add(string $method, string $path): void {
        $this->paths[$method][] = "/" . trim($path, "/");
    }

    public function version(): string {
        return $this->version;
    }

    public function authentication(): Authentication {
        return $this->authentication;
    }

    public function validPath(string $method, string $path): bool {
        $path = "/" . trim(str_replace($this->version . "/", "", $path), "/");
        return in_array($path, $this->paths[$method] ?? []);
    }

    public function paths(): array {
        return $this->paths;
    }
}