<?php declare(strict_types=1);

class Request {
    protected array $headers = [];

    public function __construct() {
        if($this->headers = getallheaders()) return;
        else if($this->headers = apache_request_headers()) return;
    }

    public function json(): array {
        $data = json_decode(file_get_contents("php://input"), true);
        if(json_last_error() == JSON_ERROR_NONE) {
            return (array) $data;
        }
        return [];
    }

    public function header(string $field): ?string {
        return $this->headers[$field] ?? null;
    }

    public function param(string $field): ?string {
        return $_SERVER["URL_PARAM"][$field] ?? null;
    }

    public function all(): array {
        return [...$_REQUEST, ...$_FILES];
    }

    public function get(string $field): mixed {
        return $_GET[$field] ?? null;
    }

    public function post(string $field): mixed {
        return $_POST[$field] ?? null;
    }

    public function file(string $field): array {
        $m_files = $_FILES[$field];
        if(!is_array($m_files)) return $m_files;

        $files = array();
        $keys = array_keys($m_files);
        for($i = 0; $i < count($m_files["name"]); $i++) {
            foreach($keys as $k) {
                $files[$i][$k] = $m_files[$k][$i];
            }
        }

        return $files;
    }
}
