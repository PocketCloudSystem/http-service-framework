<?php

namespace r3pt1s\httpclient\io;

use CurlHandle;
use InvalidArgumentException;
use r3pt1s\httpclient\util\MultipartBody;
use r3pt1s\httpserver\util\RequestMethod;
use r3pt1s\httpserver\util\StatusCode;
use r3pt1s\httpserver\util\Utils;
use RuntimeException;
use Throwable;

final class ClientRequestContext {

    private CurlHandle $curlHandle;

    public function __construct(
        private readonly string $url,
        private readonly RequestMethod $method,
        private array $headers,
        private array $queries,
        private mixed $body,
        private float $timeout,
        private int $retries
    ) {
        $this->curlHandle = curl_init();
    }

    public function prepareCurlHandle(?string &$url = null): CurlHandle {
        curl_reset($this->curlHandle);
        $queryString = empty($this->queries) ? "" : "?" . http_build_query($this->queries);
        $opts = [
            CURLOPT_URL => $url = $this->url . $queryString,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => $this->timeout
        ];

        if ($this->body !== null) {
            if ($this->body instanceof MultipartBody) {
                $opts[CURLOPT_POSTFIELDS] = $this->body->build();
            } else {
                $contentType = $this->headers["Content-Type"] ?? null;
                if (is_array($this->body) || is_object($this->body)) {
                    if ($contentType === null || str_contains($contentType, "application/json")) {
                        $opts[CURLOPT_POSTFIELDS] = json_encode($this->body);
                        $this->headers["Content-Type"] = "application/json";
                    }
                } elseif (is_string($this->body)) {
                    $opts[CURLOPT_POSTFIELDS] = $this->body;
                    if ($contentType === null) $this->headers["Content-Type"] = "text/plain";
                } elseif (is_int($this->body) || is_float($this->body) || is_bool($this->body)) {
                    $opts[CURLOPT_POSTFIELDS] = (string) $this->body;
                    $this->headers["Content-Type"] = $contentType ?? "text/plain";
                } else {
                    throw new InvalidArgumentException("Unsupported body type: " . gettype($this->body));
                }

                $this->headers["Content-Length"] = strlen($opts[CURLOPT_POSTFIELDS]);
            }
        }

        if ($this->method === RequestMethod::POST) {
            $opts[CURLOPT_POST] = true;
            if ($this->body === null && !empty($this->queries)) {
                $opts[CURLOPT_POSTFIELDS] = substr($queryString, 1);
                $this->header("Content-Type", "application/x-www-form-urlencoded");
            }
        } else if (in_array($this->method, [RequestMethod::PUT, RequestMethod::PATCH, RequestMethod::DELETE])) {
            $opts[CURLOPT_CUSTOMREQUEST] = $this->method->name;
        }

        $opts[CURLOPT_HTTPHEADER] = Utils::encodeHeaders($this->headers);
        curl_setopt_array($this->curlHandle, $opts);
        return $this->curlHandle;
    }

    public function execute(): ClientResponse|Throwable {
        $this->prepareCurlHandle($url);
        $result = curl_exec($this->curlHandle);
        $retries = $this->retries;
        if ($result === false) {
            while ($retries > 0) { // e.g. needs one retry: retries=3; retries-- -> retries = 2; takenRetries = originRetries - retries = 1
                $retries--;
                $result = curl_exec($this->curlHandle);
                if ($result !== false) break;
            }

            if ($result === false) {
                $errno = curl_errno($this->curlHandle);
                $error = curl_error($this->curlHandle);
                return new RuntimeException("cURL Error ($errno): $error");
            }
        }

        $code = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        $headers = explode("\r\n", substr($result, 0, ($len = curl_getinfo($this->curlHandle, CURLINFO_HEADER_SIZE))));
        $body = substr($result, $len);
        $decodedBody = null;
        foreach ($headers as $line) {
            if (str_contains($line, ":")) {
                [$name, $val] = explode(":", $line, 2);
                if (strtolower(trim($name)) === "content-type" && str_contains($val, "application/json")) {
                    $decodedBody = json_decode($body, true);
                }
            }
        }

        return new ClientResponse(
            $url,
            $this->method,
            StatusCode::tryFrom($code) ?? StatusCode::UNKNOWN,
            $headers,
            $body,
            $decodedBody,
            $this->retries - $retries
        );
    }

    public function hasHeader(string $name): bool {
        return isset($this->headers[$name]);
    }

    public function hasQuery(string $name): bool {
        return isset($this->queries[$name]);
    }

    public function header(string $key, string $value): self {
        $this->headers[$key] = $value;
        return $this;
    }

    public function query(string $key, mixed $value): self {
        $this->queries[$key] = $value;
        return $this;
    }

    public function removeHeader(string $name): self {
        if (isset($this->headers[$name])) unset($this->headers[$name]);
        return $this;
    }

    public function removeQuery(string $name): self {
        if (isset($this->queries[$name])) unset($this->queries[$name]);
        return $this;
    }

    public function setBody(mixed $body): self {
        $this->body = $body;
        return $this;
    }

    public function setTimeout(float $timeout): self {
        $this->timeout = $timeout;
        return $this;
    }

    public function setRetries(float $retries): self {
        $this->retries = $retries;
        return $this;
    }

    public function curlHandle(): CurlHandle {
        return $this->curlHandle;
    }

    public function url(): string {
        return $this->url;
    }

    public function method(): RequestMethod {
        return $this->method;
    }

    public function getHeader(string $name, ?string $default = null): ?string {
        return $this->headers[$name] ?? $default;
    }

    public function headers(): array {
        return $this->headers;
    }

    public function getQuery(string $name, mixed $default = null): mixed {
        return $this->queries[$name] ?? $default;
    }

    public function queries(): array {
        return $this->queries;
    }

    public function body(): mixed {
        return $this->body;
    }

    public function timeout(): float {
        return $this->timeout;
    }

    public function retries(): float {
        return $this->retries;
    }

    public function __destruct() {
        curl_close($this->curlHandle);
    }
}