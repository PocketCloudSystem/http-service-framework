<?php

namespace r3pt1s\httpserver\route;

use r3pt1s\httpserver\io\RequestContext;
use r3pt1s\httpserver\io\ResponseBuilder;
use r3pt1s\httpserver\socket\auth\Authentication;
use r3pt1s\httpserver\util\RequestMethod;
use r3pt1s\httpserver\util\StatusCode;

abstract class RegularPath implements Path {

    public function __construct(
        private readonly string $path,
        private readonly RequestMethod $requestMethod,
        private readonly Authentication $authentication
    ) {}

    public function handleFailedAuth(RequestContext $request): ResponseBuilder {
        return ResponseBuilder::create()
            ->code(StatusCode::FORBIDDEN);
    }

    final public function apiVersion(): ?string {
        return null;
    }

    public function path(): string {
        return $this->path;
    }

    public function fullPath(): string {
        return "/" . trim($this->path, "/");
    }

    public function method():  RequestMethod {
        return $this->requestMethod;
    }

    public function authentication(): Authentication {
        return $this->authentication;
    }
}