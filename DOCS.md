# PHP Boilerplate — Technical Documentation

Documentation for using this boilerplate/template. It covers the core classes in
`app/core/` and the conventions for building features on top of them.

> **Audience:** developers using this project as a starting template.
> **Project overview:** see [README.md](README.md).

---

## Table of Contents

1. [Prerequisites & Project Structure](#1-prerequisites--project-structure)
2. [Database Connection](#2-database-connection)
3. [How to Create a Repository, Controller, and Service](#3-how-to-create-a-repository-controller-and-service)
4. [How to Add Routes](#4-how-to-add-routes)
5. [Storage — `Storage::*`](#5-storage--storage)
6. [Request — Using `$request` in Controller Parameters](#6-request--using--request-in-controller-parameters)
7. [Repository & `DB::query`](#7-repository--dbquery)
8. [Validator](#8-validator)
9. [Standardized Response](#9-standardized-response)
10. [Complete Example](#10-complete-example)
11. [Gotchas & Notes](#11-gotchas--notes)

---

## 1. Prerequisites & Project Structure

### Requirements

| Requirement | Version / Note |
|---|---|
| PHP | **>= 8.1** (`mixed` types, string-keyed array unpacking) |
| Extensions | `pdo_mysql` (or matching PDO driver), `filter` |
| Web server | Apache with `mod_rewrite` (`.htaccess` provided); PHP built-in server works for local dev |
| Database | MySQL/MariaDB (any PDO-supported DSN) |

### Structure

```
.
├── index.php              # Front controller: requires core files, defines routes, runs router
├── .htaccess              # Rewrites every non-file request to index.php
├── app/
│   ├── .htaccess          # Deny from all (app/ is not web-accessible)
│   ├── controllers/       # Your controllers
│   ├── repositories/      # Your repositories
│   ├── services/          # Your services
│   └── core/              # Framework core (do not modify unless necessary)
│       ├── database.php   # class DB       — PDO wrapper, transactions, DB::query
│       ├── repository.php # class Repository — base repository (static CRUD)
│       ├── service.php    # class Service   — base service (holds a Repository)
│       ├── controller.php # class Controller— base controller (holds a Service)
│       ├── request.php    # class Request   — HTTP input access
│       ├── response.php   # class Response  — JSON / redirect responses
│       ├── router.php     # class Router    — GET/POST routing with :params
│       ├── storage.php    # class Storage   — file helpers
│       └── validator.php  # class Validator — validation rules
```

### Important: there is no autoloader

All classes live in the **global namespace**, and files are loaded manually with
`require_once` in `index.php`. **Every new class file you create must be required
there**, after the core files, respecting dependency order:

```php
// index.php
require_once __DIR__ . "/app/core/database.php";
require_once __DIR__ . "/app/core/storage.php";
require_once __DIR__ . "/app/core/validator.php";
require_once __DIR__ . "/app/core/repository.php";
require_once __DIR__ . "/app/core/service.php";
require_once __DIR__ . "/app/core/controller.php";
require_once __DIR__ . "/app/core/request.php";
require_once __DIR__ . "/app/core/response.php";
require_once __DIR__ . "/app/core/router.php";

// Your classes (repositories → services → controllers)
require_once __DIR__ . "/app/repositories/user_repository.php";
require_once __DIR__ . "/app/services/user_service.php";
require_once __DIR__ . "/app/controllers/user_controller.php";
```

File naming convention: `snake_case.php`, named after the concept
(`UserRepository` → `app/repositories/user_repository.php`).

---

## 2. Database Connection

The connection is handled by the `DB` class (`app/core/database.php`). It creates
a **lazy PDO singleton**: the connection is opened on the first query and reused
afterwards.

### Configuration

Configuration is read from **environment variables** via `getenv()`, with
fallback defaults:

| Variable | Default | Meaning |
|---|---|---|
| `DB_DRIVER` | `mysql` | PDO driver name (DSN prefix) |
| `DB_HOST` | `localhost` | Host |
| `DB_PORT` | `3306` | Port |
| `DB_NAME` | `PBL_TI_2025_B_NavetaCandra` | Database name |
| `DB_USER` | `root` | Username |
| `DB_PASS` | `root` | Password |

The DSN is built as: `"$driver:host=$host;port=$port;dbname=$dbname"`.

### Option A — Apache environment (recommended for production)

In a VirtualHost (`SetEnv` requires `mod_env`) or in `.htaccess`:

```apache
SetEnv DB_HOST localhost
SetEnv DB_PORT 3306
SetEnv DB_NAME my_app
SetEnv DB_USER root
SetEnv DB_PASS secret
```

### Option B — Development server

```bash
# Linux / macOS
DB_HOST=localhost DB_NAME=my_app DB_USER=root DB_PASS=secret php -S localhost:8000 index.php

# Windows (cmd)
set DB_HOST=localhost && set DB_NAME=my_app && php -S localhost:8000 index.php
```

### Option C — Change the defaults

Edit the fallback values in `app/core/database.php`:

```php
$driver = getenv("DB_DRIVER") ?: "mysql";
$host   = getenv("DB_HOST")    ?: "localhost";
$port   = getenv("DB_PORT")    ?: "3306";
$dbname = getenv("DB_NAME")    ?: "my_app";
$user   = getenv("DB_USER")    ?: "root";
$pass   = getenv("DB_PASS")    ?: "root";
```

### Connection behavior

- Errors: `PDO::ERRMODE_EXCEPTION` is enabled; a failed connection calls `die($message)`.
- Fetch mode: `PDO::FETCH_ASSOC` (all reads return associative arrays).
- There is **no migration/seed tool** — create your schema manually (SQL, GUI client, etc.).

### Transactions

```php
DB::beginTransaction();
try {
    DB::query("UPDATE accounts SET balance=balance-? WHERE id=?", [100, 1]);
    DB::query("UPDATE accounts SET balance=balance+? WHERE id=?", [100, 2]);
    DB::commit();
} catch (Throwable $e) {
    DB::rollBack();
    Response::json(["status" => "error", "message" => "Transfer failed"], 500);
}
```

---

## 3. How to Create a Repository, Controller, and Service

The intended layering is:

```
Route closure  →  Controller  →  Service  →  Repository  →  DB (PDO)
   (Request)      (HTTP/        (business    (data access,
                    output)      logic)       SQL)
```

### 3.1 Repository

Create `app/repositories/user_repository.php`:

```php
<?php declare(strict_types=1);

class UserRepository extends Repository {
    protected static string $table    = "users";                 // table name
    protected static string $idColumn = "id";                    // primary key column
    protected static array  $fillable = ["name", "email", "password"]; // writable columns
}
```

That is all — the base class provides all CRUD methods (see
[section 7](#7-repository--dbquery)). Then register it in `index.php`:

```php
require_once __DIR__ . "/app/repositories/user_repository.php";
```

### 3.2 Service

Create `app/services/user_service.php`:

```php
<?php declare(strict_types=1);

class UserService extends Service {
    public function __construct() {
        $this->repository = new UserRepository(); // typed as Repository in the base class
    }

    public function register(array $input): array {
        // Business rules live here, e.g. hashing, uniqueness checks, defaults.
        return [
            "name"     => trim($input["name"]),
            "email"    => strtolower(trim($input["email"])),
            "password" => password_hash($input["password"], PASSWORD_DEFAULT),
        ];
    }
}
```

> `Service` declares `protected Repository $repository;`. Assign it in the
> constructor (property initializers cannot instantiate objects). Repository
> methods are static, so calling `UserRepository::findAll()` directly from a
> service is equally valid.

### 3.3 Controller

Create `app/controllers/user_controller.php`:

```php
<?php declare(strict_types=1);

class UserController extends Controller {
    public function __construct() {
        $this->service = new UserService(); // typed as Service in the base class
    }

    public function register(Request $request): void {
        $errors = Validator::fromHTTPRequest([
            "name"  => ["required" => "Name is required!", "max:100" => "Name is too long!"],
            "email" => ["required" => "Email is required!", "email" => "Invalid email!"],
        ]);
        if ($errors !== null) {
            Response::json(["status" => "error", "errors" => $errors], 422);
            return;
        }

        $user = $this->service->register($request->all());
        $id   = UserRepository::insert($user)->lastInsertId();

        Response::json(["status" => "success", "data" => ["id" => $id]], 201);
    }
}
```

Register it in `index.php` (after repository and service):

```php
require_once __DIR__ . "/app/controllers/user_controller.php";
```

### 3.4 Check list for a new feature

1. `app/repositories/x_repository.php` — `extends Repository`, set `$table`, `$idColumn`, `$fillable`.
2. `app/services/x_service.php` — `extends Service`, assign `$this->repository`.
3. `app/controllers/x_controller.php` — `extends Controller`, assign `$this->service`,
   methods take `Request $request` and answer with `Response::*`.
4. `require_once` all three in `index.php` (repository → service → controller).
5. Add routes (next section).

---

## 4. How to Add Routes

Routes are declared in **`index.php`** with the `Router` class, between the
`require_once` block and `$router->run()`.

### Basic routes

```php
$router = new Router();

$router->get("/", function () {
    Response::json(["status" => "success", "data" => ["app" => "php-boilerplate"]]);
});

$router->post("/login", function (Request $request) {
    // ...
});
```

### Route parameters (`:name`)

Placeholders use the `:name` pattern. They are extracted by the router and read
with `$request->param("name")`:

```php
$router->get("/users/:id", function (Request $request) {
    $user = UserRepository::findOneById($request->param("id"));
    if (!$user) Response::json(["status" => "error", "message" => "Not found"], 404);
    else Response::json(["status" => "success", "data" => $user]);
});

$router->get("/users/:id/posts/:postId", function (Request $request) {
    $userId = $request->param("id");
    $postId = $request->param("postId");
    // ...
});
```

### Dispatching to a controller

A handler is any `callable` and **always receives one argument: a `Request`
instance**. The idiomatic pattern is a closure that delegates to a controller
method:

```php
$router->post("/users",       fn (Request $r) => (new UserController())->register($r));
$router->get ("/users/:id",   fn (Request $r) => (new UserController())->show($r));
```

Array callables also work:

```php
$router->get("/users", [new UserController(), "index"]);
```

### Route rules

| Rule | Detail |
|---|---|
| Supported verbs | `GET` and `POST` only |
| Matching order | Registration order — **first match wins**. Register static paths (e.g. `/users/me`) **before** parameterized ones (e.g. `/users/:id`) |
| Trailing slashes | Ignored (`/users/` matches `/users`) |
| Subdirectory installs | Handled automatically; build links/redirects with the `BASE_URL` constant defined by the router |
| No match | HTTP `404` is returned automatically |
| Method not registered | e.g. a `PUT` request when only `GET`/`POST` exist → treated as no match (404) |

`BASE_URL` is available inside handlers:

```php
Response::redirect(BASE_URL . "/dashboard"); // works in root or subdirectory installs
```

---

## 5. Storage — `Storage::*`

`Storage` (`app/core/storage.php`) is a static filesystem helper.

| Method | Signature | Returns | Description |
|---|---|---|---|
| `exists` | `Storage::exists(string $path): bool` | `bool` | `file_exists()` |
| `get` | `Storage::get(string $path): mixed` | `string\|false` | Read whole file contents |
| `put` | `Storage::put(string $path, string $content): bool` | `bool` | Write/overwrite file contents |
| `save` | `Storage::save(string $from, string $dest): bool` | `bool` | Move an **uploaded** file (`move_uploaded_file`) |
| `unlink` | `Storage::unlink(string $path): bool` | `bool` | Delete file; returns `true` if the file already did not exist |

### Examples

```php
// Write / read
Storage::put("storage/cache/token.txt", "abc123");
$token = Storage::get("storage/cache/token.txt");

// Check & delete
if (Storage::exists("storage/cache/token.txt")) {
    Storage::unlink("storage/cache/token.txt");
}
```

### File upload flow

```php
$router->post("/attachments", function (Request $request) {
    $files = $request->file("documents"); // normalized list of uploaded files

    $errors = Validator::validate($files, [
        "required"    => "File is required!",
        "pdf"         => "Only PDF files are allowed!",
        "max_size:2048" => "File is larger than 2 MB!",
        "min_files:1" => "At least one file!",
    ]);
    if ($errors !== null) {
        Response::json(["status" => "error", "errors" => ["documents" => $errors]], 422);
        return;
    }

    $saved = [];
    foreach ($files as $file) {
        $dest = "uploads/" . uniqid() . "_" . basename($file["name"]);
        if (!Storage::save($file["tmp_name"], $dest)) {
            Response::json(["status" => "error", "message" => "Upload failed"], 500);
            return;
        }
        $saved[] = $dest;
    }

    Response::json(["status" => "success", "data" => ["files" => $saved]], 201);
});
```

### Notes

- The destination directory must already exist — create it once
  (`mkdir("uploads", 0775, true)` or ship an empty `uploads/` with a `.htaccess`).
- Paths are relative to the working directory (project root). Prefer explicit
  paths, e.g. `"uploads/" . $name`.
- Files inside `app/` cannot be downloaded (`app/.htaccess` denies all); store
  public uploads **outside** `app/`. Existing files are served directly by the
  root `.htaccess` (`RewriteCond %{REQUEST_FILENAME} !-f`).
- Use `save()` only for real HTTP uploads; use `put()` for regular writes.

---

## 6. Request — Using `$request` in Controller Parameters

`Request` (`app/core/request.php`) is instantiated by the router and passed as
the **single argument to every route handler**. Pass it through to your
controller methods by declaring it as the `$request` parameter:

```php
$router->get("/users/:id", fn (Request $request) => (new UserController())->show($request));

// app/controllers/user_controller.php
class UserController extends Controller {
    public function show(Request $request): void {
        $id = $request->param("id");
        // ...
    }
}
```

### API

| Method | Signature | Description |
|---|---|---|
| `param` | `param(string $field): ?string` | Read a **route parameter** (`:field` placeholder) |
| `get` | `get(string $field): mixed` | Read a `$_GET` (query string) value |
| `post` | `post(string $field): mixed` | Read a `$_POST` (form body) value |
| `all` | `all(): array` | Merged `$_REQUEST` + `$_FILES` (query, body and files) |
| `file` | `file(string $field): array` | Normalized uploaded file(s) for `$_FILES[$field]` |
| `header` | `header(string $field): ?string` | Read a request header (case-sensitive, as received) |

### Examples

```php
public function show(Request $request): void {
    $id     = $request->param("id");          // /users/42 -> "42"
    $sort   = $request->get("sort");          // ?sort=name
    $name   = $request->post("name");         // form field
    $token  = $request->header("Authorization");
    $input  = $request->all();                // everything at once
    $files  = $request->file("avatar");       // uploaded file(s)
}
```

### `file()` output shape

`file()` flattens PHP's nested `$_FILES` structure into a list of file entries —
exactly the shape the `Validator` file rules expect:

```php
$request->file("avatar");
// [
//   ["name" => "a.png", "type" => "image/png", "tmp_name" => "/tmp/phpXXX",
//    "error" => 0, "size" => 12345],
// ]
```

### Notes

- `get()`, `post()`, `param()` and `header()` do **not** apply defaults — a
  missing key raises a PHP warning and returns `null`. Validate first
  (`Validator`) or read through `all()`.
- `header()` matches the exact header case sent by the client
  (e.g. `"Content-Type"`, `"Authorization"`).

---

## 7. Repository & `DB::query`

### 7.1 Base repository

`Repository` (`app/core/repository.php`) is a static CRUD base class. Configure
it with three static properties:

| Property | Type | Meaning |
|---|---|---|
| `$table` | `string` | Table name (default `"example"`) |
| `$idColumn` | `string` | Primary key column (default `"id"`) |
| `$fillable` | `array` | Columns allowed for insert/update |

### 7.2 Available methods

All methods are **static** and use late static binding, so they operate on the
child class's configuration.

| Method | Signature | Returns | Description |
|---|---|---|---|
| `findAll` | `findAll(): array` | `array` | All rows of the table |
| `findOneById` | `findOneById(string $id): mixed` | `array\|false` | Row by primary key (`LIMIT 1`) |
| `find` | `find(array $data): array` | `array` | Rows matching **all** given conditions (AND) |
| `findOne` | `findOne(array $data): array` | `array\|false` | First row matching the conditions |
| `insert` | `insert(array $data): PDOStatement` | statement | Insert one row using `$fillable` |
| `update` | `update(string $id, array $data): PDOStatement` | statement | Update row by primary key using `$fillable` |
| `delete` | `delete(string $id): PDOStatement` | statement | Delete row by primary key |

### 7.3 Usage

```php
UserRepository::findAll();                        // SELECT * FROM users
UserRepository::findOneById("42");                // SELECT * FROM users WHERE id='42' LIMIT 1

// Exact match conditions (keyed by column name)
UserRepository::findOne(["email" => "a@b.c"]);    // SELECT * FROM users WHERE email=? LIMIT 1
UserRepository::find(["role" => "admin", "active" => 1]);

// Operators: value is [operator, operand] -> "column OPERATOR :column"
UserRepository::find(["age" => [">", 18]]);       // WHERE age > ?
UserRepository::find(["name" => ["LIKE", "%john%"]]);
UserRepository::find(["score" => [">=", 90], "role" => "member"]);
```

Conditions are always combined with `AND`.

### 7.4 Insert / update constraints

```php
// insert(): values are bound positionally — provide them IN THE $fillable ORDER.
UserRepository::insert(["Budi", "budi@mail.com", "secret"]);
// INSERT INTO users (name, email, password) VALUES (?, ?, ?)

// update(): binds NAMED parameters for every $fillable column,
// so ALL fillable columns must be present in $data.
UserRepository::update("42", [
    "name"     => "Budi Santoso",
    "email"    => "budi@mail.com",
    "password" => "secret",
]);
// UPDATE users SET name=:name, email=:email, password=:password WHERE id=?

UserRepository::delete("42");                      // DELETE FROM users WHERE id=?
```

### 7.5 Customizing a repository

Override properties, override inherited methods, or add your own queries:

```php
<?php declare(strict_types=1);

class UserRepository extends Repository {
    protected static string $table    = "users";
    protected static string $idColumn = "id";
    protected static array  $fillable = ["name", "email", "password"];

    // Custom query: anything DB::query can do
    public static function findActiveAdmins(): array {
        return DB::query(
            "SELECT id, name, email FROM users WHERE role = ? AND active = 1 ORDER BY name",
            ["admin"]
        )->fetchAll();
    }

    public static function emailExists(string $email): bool {
        return (bool) DB::query(
            "SELECT 1 FROM users WHERE email = ? LIMIT 1",
            [$email]
        )->fetch();
    }

    // Override an inherited method entirely
    public static function findAll(): array {
        return DB::query("SELECT id, name, email FROM users ORDER BY id")->fetchAll();
    }
}
```

### 7.6 `DB::query` directly

`DB::query(string $sql = "", array $parameter = []): PDOStatement` prepares and
executes SQL with bound parameters. **Always use placeholders — never interpolate
user input into SQL.**

```php
// Positional placeholders (?)
$rows = DB::query("SELECT * FROM users WHERE age > ? AND city = ?", [18, "Bandung"])->fetchAll();

// Named placeholders (:name)
$row  = DB::query("SELECT * FROM users WHERE id = :id LIMIT 1", ["id" => 42])->fetch();

// Fetch modes
$stmt = DB::query("SELECT * FROM users");
$all      = $stmt->fetchAll();                 // all rows (assoc, configured by default)
$one      = $stmt->fetch();                    // one row or false
$count    = DB::query("SELECT COUNT(*) c FROM users")->fetch()["c"];

// Writes
$stmt = DB::query("INSERT INTO users (name) VALUES (?)", ["Sari"]);
$newId = $stmt->lastInsertId();
$affected = DB::query("UPDATE users SET name=? WHERE id=?", ["Sari", 1])->rowCount();

// Transactions
DB::beginTransaction();
try {
    // ... multiple DB::query() calls ...
    DB::commit();
} catch (Throwable $e) {
    DB::rollBack();
    throw $e;
}
```

There is **no fluent query builder** — `DB::query` + raw SQL (or the base
repository) is the data-access API.

---

## 8. Validator

`Validator` (`app/core/validator.php`) validates data against **rule => message**
maps. The message is what gets returned when the rule fails.

### Entry points

| Method | Input | Success | Failure |
|---|---|---|---|
| `Validator::validate($data, $rules)` | one value | `null` | `string` (first failing rule's message) |
| `Validator::validateAll($data, $rules)` | array of values + rules per field | `null` | `array` of `field => message` |
| `Validator::fromHTTPRequest($rules)` | reads `$_REQUEST` / `$_FILES` directly | `null` | `array` of `field => message` |

### Rules

| Rule | Validates | Example message key |
|---|---|---|
| `required` | value present and not empty/blank | `"required"` |
| `string` | value is a `string` | `"string"` |
| `int` | value is an `int` (strict PHP type) | `"int"` |
| `float` | value is a `float` | `"float"` |
| `email` | valid e-mail address | `"email"` |
| `min:N` | number **or** string length >= N | `"min:5"` |
| `max:N` | number **or** string length <= N | `"max:100"` |
| `enum:a,b,c` | string contained in the list | `"enum:admin,user,guest"` |
| `pattern:REGEX` | string matches the regex (**without delimiters**) | `"pattern:[A-Za-z]+"` |
| `image` | file MIME type starts with `image/` | `"image"` |
| `pdf` | file MIME type is `application/pdf` | `"pdf"` |
| `mime:type1,type2` | file MIME type is in the list | `"mime:image/png,image/jpeg"` |
| `min_size:N` / `max_size:N` | file size >= / <= N bytes (all files if multiple) | `"max_size:2048"` |
| `min_files:N` / `max_files:N` | number of uploaded files | `"min_files:1"` |

**Behavior notes**

- If a value is empty/`null` and `required` is **not** among its rules, the field
  passes automatically.
- Rules are checked in order; the first failure wins.
- `int`/`float` check the *PHP type*. HTTP input is always a **string**, so for
  request data prefer `required` + `pattern:\d+` (or validate inside the service
  after casting).

### Example: `fromHTTPRequest()` (typical controller usage)

```php
$errors = Validator::fromHTTPRequest([
    "name"  => ["required" => "Name is required!", "max:100" => "Name is too long!"],
    "email" => ["required" => "Email is required!", "email" => "Email is invalid!"],
    "role"  => ["required" => "Role is required!", "enum:admin,user,guest" => "Unknown role!"],
    // "file:avatar" reads $_FILES["avatar"] instead of $_REQUEST
    "file:avatar" => ["image" => "Avatar must be an image!", "max_size:2048" => "Max 2 MB!"],
]);

if ($errors !== null) {
    Response::json(["status" => "error", "errors" => $errors], 422);
    return;
}
```

The returned `$errors` array is designed to be sent directly as the `errors`
object of a 422 JSON response.

### Example: `validate()` with a single value

```php
$error = Validator::validate($request->post("age"), [
    "required" => "Age is required!",
    "pattern:\\d+" => "Age must be a number!",
    "max:3"    => "Age is invalid!",
]);
if ($error !== null) Response::json(["status" => "error", "message" => $error], 422);
```

### Example: `validate()` with files from `Request::file()`

```php
$files = $request->file("documents");
$errors = Validator::validate($files, [
    "required"      => "Documents are required!",
    "pdf"           => "Only PDF allowed!",
    "min_files:1"   => "Upload at least 1 file!",
    "max_files:5"   => "Maximum 5 files!",
]);
```

### Example: `validateAll()` on an arbitrary array

```php
$errors = Validator::validateAll($input, [
    "username" => ["required" => "Username required!", "min:4" => "Too short!"],
    "password" => ["required" => "Password required!", "min:8" => "Min 8 characters!"],
]);
```

---

## 9. Standardized Response

`Response` (`app/core/response.php`) is the **single way to answer a request**.
Both methods set the HTTP status/header and emit the body — they do not return
the body, so end every handler with one of them.

| Method | Signature | Behavior |
|---|---|---|
| `json` | `Response::json(array $data, int $status = 200)` | Sets `$status`, `Content-Type: application/json`, echoes `json_encode($data)` |
| `redirect` | `Response::redirect(string $dest, int $status = 302)` | Sets `$status` and `Location: $dest` |

### Recommended JSON envelope

This project does not enforce a schema; the conventional shape used throughout
the documentation is:

```php
// Success
Response::json(["status" => "success", "data" => $payload], 200);   // or 201 on create

// Failure
Response::json(["status" => "error", "message" => "Something went wrong"], 500);

// Validation failure (errors array straight from Validator)
Response::json(["status" => "error", "errors" => $errors], 422);
```

### Typical status codes

| Code | Use |
|---|---|
| `200` | OK — read/update succeeded |
| `201` | Created — after `insert()` |
| `302` | Redirect (default of `redirect()`) |
| `400` | Malformed/invalid input that is not field-level validation |
| `401` | Not authenticated |
| `403` | Forbidden |
| `404` | Resource/route not found (router also emits 404 automatically) |
| `422` | Validation errors (`Validator`) |
| `500` | Server error |

### Examples

```php
// JSON
$user = UserRepository::findOneById($request->param("id"));
if (!$user) Response::json(["status" => "error", "message" => "User not found"], 404);
else Response::json(["status" => "success", "data" => $user]);

// Redirect (BASE_URL keeps subdirectory installs working)
Response::redirect(BASE_URL . "/login");
Response::redirect(BASE_URL . "/users/" . $id, 303);
```

Handlers usually `return` the call for clarity:

```php
$router->get("/ping", fn () => Response::json(["status" => "success", "data" => ["pong" => true]]));
```

---

## 10. Complete Example

End-to-end "create user" feature:

```php
// app/repositories/user_repository.php
<?php declare(strict_types=1);
class UserRepository extends Repository {
    protected static string $table    = "users";
    protected static string $idColumn = "id";
    protected static array  $fillable = ["name", "email", "password"];
}

// app/services/user_service.php
<?php declare(strict_types=1);
class UserService extends Service {
    public function __construct() {
        $this->repository = new UserRepository();
    }

    public function create(array $input): string {
        $data = [
            trim($input["name"]),
            strtolower(trim($input["email"])),
            password_hash($input["password"], PASSWORD_DEFAULT),
        ];
        return UserRepository::insert($data)->lastInsertId();
    }
}

// app/controllers/user_controller.php
<?php declare(strict_types=1);
class UserController extends Controller {
    public function __construct() {
        $this->service = new UserService();
    }

    public function index(Request $request): void {
        Response::json(["status" => "success", "data" => UserRepository::findAll()]);
    }

    public function store(Request $request): void {
        $errors = Validator::fromHTTPRequest([
            "name"     => ["required" => "Name is required!", "max:100" => "Name too long!"],
            "email"    => ["required" => "Email is required!", "email" => "Invalid email!"],
            "password" => ["required" => "Password is required!", "min:8" => "Min 8 characters!"],
        ]);
        if ($errors !== null) {
            Response::json(["status" => "error", "errors" => $errors], 422);
            return;
        }

        $id = $this->service->create($request->all());
        Response::json(["status" => "success", "data" => ["id" => $id]], 201);
    }
}

// index.php (excerpt)
require_once __DIR__ . "/app/repositories/user_repository.php";
require_once __DIR__ . "/app/services/user_service.php";
require_once __DIR__ . "/app/controllers/user_controller.php";

$router = new Router();
$router->get("/users",     fn (Request $r) => (new UserController())->index($r));
$router->post("/users",    fn (Request $r) => (new UserController())->store($r));
$router->run();
```

---

## 11. Gotchas & Notes

1. **No autoloader** — new classes must be `require_once`d in `index.php`, in
   dependency order (repository → service → controller).
2. **No middleware / route groups** — only `GET` and `POST`; cross-cutting checks
   (auth, CORS) must be done inside handlers or the front controller.
3. **Route order matters** — first registered match wins; declare static paths
   before `:param` paths.
4. **`insert()` is positional** — pass values in `$fillable` order.
5. **`update()` needs every fillable column** — it binds `:name` placeholders for
   all of `$fillable`; missing keys raise a PDO error. Keep `$fillable` narrow or
   override `update()` for partial updates.
6. **HTTP input is strings** — strict `int`/`float` rules fail on query/body
   values, and `min:N`/`max:N` measure **string length** for strings. Use
   `pattern:\d+` for numeric fields coming from the request, then cast.
7. **Missing request keys warn** — `$request->post("x")` on a missing key emits an
   "undefined array key" warning; validate with `Validator` first.
8. **`Response::json()` echoes** — it does not return the payload; call it as the
   last statement of a handler.
9. **Upload directories must exist** — `Storage::save()`/`put()` fail if the
   target directory was not created beforehand.
10. **`app/` is not web-accessible** — keep public assets/uploads outside it.
11. **Schema management is manual** — no migrations, seeds, or CLI tooling are
    included.
12. **Fail-fast database** — a connection error `die()`s with the PDO message;
    never expose that in production (handle it or customize `DB::init()`).
