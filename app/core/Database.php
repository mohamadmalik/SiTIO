<?php declare(strict_types=1);

class DB {
    protected static ?PDO $pdo = null;

    protected static function init() {
        $driver = getenv("DB_DRIVER") ?: "mysql";
        $host = getenv("DB_HOST") ?: "localhost";
        $port = getenv("DB_PORT") ?: "3306";
        $dbname = getenv("DB_NAME") ?: "PBL_TI_2025_B_NavetaCandra";
        $user = getenv("DB_USER") ?: "root";
        $pass = getenv("DB_PASS") ?: "root";

        $dsn = "$driver:host=$host;port=$port;dbname=$dbname";
        try {
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo = $pdo;
        } catch(PDOException $e) {
            Log::error("PDO connection failed.", [
                "error_code" => $e->getCode(),
                "error_message" => $e->getMessage()
            ]);
            http_response_code(502);
            die();
        }
    }

    protected static function getConnection(): ?PDO {
        if(self::$pdo === null) self::init();
        return self::$pdo;
    }

    public static function beginTransaction() {
        $pdo = self::getConnection();
        $pdo->beginTransaction();
    }

    public static function commit() {
        $pdo = self::getConnection();
        $pdo->commit();
    }

    public static function rollBack() {
        $pdo = self::getConnection();
        $pdo->rollBack();
    }

    public static function query(string $sql="", array $parameter=[]): PDOStatement {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameter);
        return $stmt;
    }
}
