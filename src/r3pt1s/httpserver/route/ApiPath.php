<?php

namespace r3pt1s\httpserver\route;

use r3pt1s\httpserver\io\RequestContext;
use r3pt1s\httpserver\io\ResponseBuilder;
use r3pt1s\httpserver\socket\auth\Authentication;
use r3pt1s\httpserver\util\RequestMethod;
use r3pt1s\httpserver\util\StatusCode;

abstract class ApiPath implements Path {

    public function __construct(
        private readonly string $path,
        private readonly string $version,
        private readonly RequestMethod $requestMethod,
        private readonly Authentication $authentication
    ) {}

    public function handleFailedAuth(RequestContext $request): ResponseBuilder {
        return ResponseBuilder::create()
            ->code(StatusCode::FORBIDDEN);
    }

    public function path(): string {
        return $this->path;
    }

    public function fullPath(): string {
        return "/" . $this->apiVersion() . "/" . trim($this->path(), "/");
    }

    public function apiVersion(): string {
        return $this->version;
    }

    public function method(): RequestMethod {
        return $this->requestMethod;
    }

    public function authentication(): Authentication {
        return $this->authentication;
    }
}