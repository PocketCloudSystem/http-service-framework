<?php

namespace r3pt1s\httpclient\util;

use CURLFile;

final class MultipartBody {

    private array $fields = [];
    private array $files = [];

    private function __construct() {}

    public function field(string $name, string $value): self {
        $this->fields[$name] = $value;
        return $this;
    }

    public function file(string $name, string $path, ?string $filename = null): self {
        $this->files[$name] = new CURLFile($path, mime_content_type($path), $filename ?? basename($path));
        return $this;
    }

    public function build(): array {
        return array_merge($this->fields, $this->files);
    }

    public static function create(): self {
        return new self();
    }
}