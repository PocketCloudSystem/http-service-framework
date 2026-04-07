<?php

namespace r3pt1s\httpserver\route;

use r3pt1s\httpserver\io\RequestContext;
use r3pt1s\httpserver\io\ResponseBuilder;
use r3pt1s\httpserver\socket\auth\Authentication;
use r3pt1s\httpserver\util\RequestMethod;

interface Path {

    public function handle(RequestContext $request): ResponseBuilder;

    public function handleFailedAuth(RequestContext $request): ResponseBuilder;

    public function isBadRequest(RequestContext $request, ResponseBuilder $response): bool;

    public function willCauseError(RequestContext $request, ResponseBuilder $response): bool;

    public function apiVersion(): ?string;

    public function path(): string;

    public function fullPath(): string;

    public function method(): RequestMethod;

    public function authentication(): Authentication;
}