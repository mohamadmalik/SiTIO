<?php declare(strict_types=1);

class Repository {
    protected static string $table = "example";
    protected static string $idColumn = "id";
    protected static array $fillable = [];

    public static function findAll(): array {
        $table = static::$table;
        return DB::query("SELECT * FROM $table")->fetchAll();
    }

    public static function findOneById(string $id): mixed {
        $table = static::$table;
        $idColumn = static::$idColumn;
        return DB::query("SELECT * FROM $table WHERE $idColumn=? LIMIT 1", [$id])->fetch();
    }

    public static function find(array $data): array {
        $table = static::$table;
        $m_whereClause = array_map(
            fn ($col) => !is_array($data[$col])
                ? "$col=:$col" // exact
                : "$col {$data[$col][0]} :$col", // <, >, <=, >=, LIKE, ...
            array_keys($data)
        );
        $whereClause = implode(" AND ", $m_whereClause);

        $m_data = array_map(
            fn ($val) => !is_array($val) ? $val : $val[1],
            array_values($data)
        );

        return DB::query("SELECT * FROM $table WHERE $whereClause", $m_data)->fetchAll();
    }

    public static function findOne(array $data): array {
        $table = static::$table;
        $m_whereClause = array_map(
            fn ($col) => "$col=:$col",
            array_keys($data)
        );
        $whereClause = implode(" AND ", $m_whereClause);

        return DB::query("SELECT * FROM $table WHERE $whereClause LIMIT 1", $data)->fetch();
    }

    public static function insert(array $data): PDOStatement {
        $table = static::$table;
        $cols = implode(", ", static::$fillable);
        $params = implode(", ",
            array_map(
                fn () => "?",
                static::$fillable
            )
        );

        return DB::query("INSERT INTO $table ($cols) VALUES ($params)", $data);
    }

    public static function update(string $id, array $data): PDOStatement {
        $table = static::$table;
        $idColumn = static::$idColumn;
        $cols = implode(", ",
            array_map(
                fn ($col) => "$col=:$col",
                static::$fillable
            )
        );

        return DB::query("UPDATE $table SET $cols WHERE $idColumn=?", [...$data, $id]);
    }

    public static function delete(string $id): PDOStatement {
        return DB::query("DELETE FROM ${static::$table} WHERE ${static::$idColumn}=?", [$id]);
    }
}
