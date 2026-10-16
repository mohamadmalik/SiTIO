<?php declare(strict_types=1);

class Response {
    public static function json(array $data, int $status = 200) {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data);
    }

    public static function redirect(string $dest, int $status = 302) {
        http_response_code($status);
        header("Location: $dest");
    }
}
