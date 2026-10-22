<?php declare(strict_types=1);

class Storage {
    public static function exists(string $path): bool {
        return file_exists($path);
    }

    public static function unlink(string $path): bool {
        if(!self::exists($path)) return true;
        if(unlink($path)) return true;
        return false;
    }

    public static function get(string $path): mixed {
        return file_get_contents($path);
    }

    public static function put(string $path, string $content): bool {
        if(file_put_contents($path, $content)) return true;
        return false;
    }

    public static function save(string $from, string $dest): bool {
        if(move_uploaded_file($from, $dest)) return true;
        return false;
    }
}
