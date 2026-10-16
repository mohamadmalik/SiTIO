<?php declare(strict_types=1);

class Request {
    public function all(): array {
        return [...$_REQUEST, ...$_FILES];
    }

    public function get(string $field): string|int|float {
        return $_GET[$field];
    }

    public function post(string $field): string|int|float {
        return $_POST[$field];
    }

    public function file(string $field): array {
        return $_FILES[$field];
    }
}
