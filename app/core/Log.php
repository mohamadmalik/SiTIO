<?php declare(strict_types=1);

final class Log {
    // private const LOG_FILE = 'php://stderr'; // terminal
    private const LOG_FILE = __DIR__.'/../../storage/system.log'; // file

    private static function log(string $level, string $message, array $details = []): void {
        global $requestId;
        $context = [
            "level" => $level,
            "requestId" => $requestId ?? null,
            "timestamp" => date("Y-M-d H:i:s", time()),
            "message" => $message,
            "details" => $details,
        ];
        $json = json_encode(
            $context,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        file_put_contents(
            self::LOG_FILE,
            $json . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    public static function info(string $message, array $details = []): void {
        self::log("INFO", $message, $details);
    }

    public static function warn(string $message, array $details = []): void {
        self::log("WARN", $message, $details);
    }

    public static function error(string $message, array $details = []): void {
        self::log("ERROR", $message, $details);
    }
}
