<?php

namespace r3pt1s\httpserver\event\def;

use r3pt1s\httpserver\event\Event;
use r3pt1s\httpserver\io\RequestContext;
use r3pt1s\httpserver\socket\SocketClient;
use Throwable;

final class RequestErrorEvent extends Event {

    public function __construct(
        private readonly SocketClient $client,
        private readonly ?RequestContext $requestContext,
        private readonly Throwable $exception
    ) {}

    public function getClient(): SocketClient {
        return $this->client;
    }

    public function getRequest(): ?RequestContext {
        return $this->requestContext;
    }

    public function getException(): Throwable {
        return $this->exception;
    }
}