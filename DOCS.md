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
5. [Views & `Response::render`](#5-views--responserender)
6. [Request — Using `$request` in Handlers](#6-request--using-request-in-handlers)
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
│   ├── views/             # Views (*.view.php)
│   └── core/              # Framework core (do not modify unless necessary)
│       ├── Database.php   # class DB       — PDO wrapper, transactions, DB::query
│       ├── Repository.php # class Repository — base repository (static CRUD)
│       ├── Service.php    # class Service   — base service (holds a Repository)
│       ├── Controller.php # class Controller— base controller (holds a Service)
│       ├── Request.php    # class Request   — HTTP input access
│       ├── Response.php   # class Response  — JSON / redirect / render responses
│       ├── Router.php     # class Router    — routing with :params and *
│       ├── Storage.php    # class Storage   — file helpers
│       └── Validator.php  # class Validator — validation rules
```

### Important: there is no autoloader

All classes live in the **global namespace**, and files are loaded manually with
`require_once` in `index.php` — core files first, in dependency order:

```php
// index.php
require_once __DIR__ . "/app/core/Database.php";
require_once __DIR__ . "/app/core/Storage.php";
require_once __DIR__ . "/app/core/Validator.php";
require_once __DIR__ . "/app/core/Repository.php";
require_once __DIR__ . "/app/core/Service.php";
require_once __DIR__ . "/app/core/Controller.php";
require_once __DIR__ . "/app/core/Request.php";
require_once __DIR__ . "/app/core/Response.php";
require_once __DIR__ . "/app/core/Router.php";
```

Controllers referenced from a route as `["UserController", "store"]` are loaded
lazily by the router (`require_once app/controllers/<ClassName>.php`), so they do
**not** need to be required in `index.php`. Repositories and services still do:

```php
require_once __DIR__ . "/app/repositories/UserRepository.php";
require_once __DIR__ . "/app/services/UserService.php";
```

File naming convention: the file name matches the class name
(`UserRepository` → `app/repositories/UserRepository.php`).

---

## 2. Database Connection

`DB` (`app/core/Database.php`) creates a **lazy PDO singleton**: the connection
is opened on the first query and reused afterwards.

### Configuration

Read from **environment variables** via `getenv()`, with fallback defaults:

| Variable | Default | Meaning |
|---|---|---|
| `DB_DRIVER` | `mysql` | PDO driver name (DSN prefix) |
| `DB_HOST` | `localhost` | Host |
| `DB_PORT` | `3306` | Port |
| `DB_NAME` | `PBL_TI_2025_B_NavetaCandra` | Database name |
| `DB_USER` | `root` | Username |
| `DB_PASS` | `root` | Password |

The DSN is `"$driver:host=$host;port=$port;dbname=$dbname"`.

### Option A — Apache environment

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

Edit the fallback values in `app/core/Database.php` (`DB::init()`).

### Connection behavior

- `PDO::ERRMODE_EXCEPTION`; a failed connection calls `die($message)`.
- `PDO::FETCH_ASSOC` — reads return associative arrays.
- **No migration/seed tool** — create your schema manually.

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
Route  →  Controller  →  Service  →  Repository  →  DB (PDO)
```

### 3.1 Repository

`app/repositories/UserRepository.php`:

```php
<?php declare(strict_types=1);

class UserRepository extends Repository {
    protected static string $table    = "users";
    protected static string $idColumn = "id";
    protected static array  $fillable = ["name", "email", "password"];
}
```

That is all — the base class provides all CRUD methods (see
[section 7](#7-repository--dbquery)). Register it in `index.php`:

```php
require_once __DIR__ . "/app/repositories/UserRepository.php";
```

### 3.2 Service

`app/services/UserService.php`:

```php
<?php declare(strict_types=1);

class UserService extends Service {
    public function __construct() {
        $this->repository = new UserRepository(); // typed as Repository in the base class
    }

    public function register(array $input): array {
        return [
            "name"     => trim($input["name"]),
            "email"    => strtolower(trim($input["email"])),
            "password" => password_hash($input["password"], PASSWORD_DEFAULT),
        ];
    }
}
```

> `Service` declares `protected Repository $repository;`. Assign it in the
> constructor. Repository methods are static, so calling `UserRepository::all()`
> directly from a service is equally valid.

### 3.3 Controller

`app/controllers/UserController.php`:

```php
<?php declare(strict_types=1);

class UserController extends Controller {
    public function __construct() {
        $this->service = new UserService();
    }

    public function register(Request $request, array $params): void {
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

### 3.4 Check list for a new feature

1. `app/repositories/XRepository.php` — `extends Repository`, set `$table`, `$idColumn`, `$fillable`; require it in `index.php`.
2. `app/services/XService.php` — `extends Service`, assign `$this->repository`; require it in `index.php`.
3. `app/controllers/XController.php` — `extends Controller`, assign `$this->service`; loaded by the router.
4. Add routes (next section).

---

## 4. How to Add Routes

Routes are declared in **`index.php`** between the `require_once` block and
`$router->run()`.

### Available methods

```php
$router = new Router();
$router->get("/path", ...$handlers);
$router->post("/path", ...$handlers);
$router->put("/path", ...$handlers);
$router->patch("/path", ...$handlers);
$router->delete("/path", ...$handlers);
```

Each handler is a `callable` or an `[ClassName, "method"]` array, and **every
handler receives `(Request $request, array $params)`**.

```php
$router->get("/", fn (Request $r, array $p) =>
    Response::json(["status" => "success", "data" => ["app" => "php-boilerplate"]]));
```

### Multiple handlers

Handlers run in order; `json()`/`redirect()` exit, while `render()` and plain
handlers return and let the next handler run:

```php
$router->get("/dashboard", $auth, $loadUser, fn (Request $r, array $p) => Response::render("dashboard"));
```

### Route parameters (`:name`) and wildcards (`*`)

```php
$router->get("/users/:id", function (Request $r, array $p) {
    $id = $p["id"];                    // /users/42 -> "42"
    $user = UserRepository::findOneById($id);
    if (!$user) Response::json(["status" => "error", "message" => "Not found"], 404);
    else Response::json(["status" => "success", "data" => $user]);
});

$router->get("/files/*", fn (Request $r, array $p) => Response::json(["status" => "success"]));
```

Parameters are positional and passed as the `$params` array in the order they
appear in the route (e.g. `/users/:id/posts/:postId` → `$p["id"]`, `$p["postId"]`).

### Dispatching to a controller

```php
$router->post("/users",    ["UserController", "store"]);
$router->get("/users/:id", ["UserController", "show"]);
```

The router instantiates the controller with no arguments and calls the method
with `($request, $params)`, so controller actions must accept both.

### Route rules

| Rule | Detail |
|---|---|
| Supported verbs | `GET`, `POST`, `PUT`, `PATCH`, `DELETE` |
| Method override | A `POST` containing `_method=PUT\|PATCH\|DELETE` is dispatched as that verb |
| Matching order | Registration order — **first match wins**. Register static paths (e.g. `/users/me`) **before** parameterized ones |
| Placeholders | `:name` matches one path segment; `*` matches anything |
| Subdirectory installs | Handled automatically; build links/redirects with the `BASE_URL` constant |
| Existing files | For `GET`, if the requested path is an existing file the router returns `false` and lets the web server serve it |
| No match | HTTP `404` is returned automatically |

---

## 5. Views & `Response::render`

`Response::render(string $view, array $data = [], int $status = 200)` renders
`app/views/<view>.view.php`. Dots map to subdirectories
(`"admin/users"` → `app/views/admin/users.view.php`).

```php
$router->get("/sample", ["SampleController", "test"]);
// app/controllers/SampleController.php
class SampleController extends Controller {
    public function test(Request $request, array $params) {
        Response::render("sample", ["title" => "Hello"]);
    }
}
```

Inside the view, every key of `$data` is available as a variable, and a
`$request` instance is provided:

```php
<!-- app/views/sample.view.php -->
<h1><?= $title ?></h1>
<p><b>User-Agent:</b> <?= $request->header("User-Agent") ?></p>
```

---

## 6. Request — Using `$request` in Handlers

`Request` (`app/core/Request.php`) is created by the router and passed to each
handler.

| Method | Signature | Description |
|---|---|---|
| `get` | `get(string $field): mixed` | Read a `$_GET` (query string) value |
| `post` | `post(string $field): mixed` | Read a `$_POST` (form body) value |
| `json` | `json(): array` | Decode the raw request body as JSON (empty array on error) |
| `all` | `all(): array` | Merged `$_REQUEST` + `$_FILES` |
| `file` | `file(string $field): array` | Uploaded file(s) for `$_FILES[$field]` |
| `header` | `header(string $field): ?string` | Read a request header (case-sensitive) |

Route parameters are **not** read from `Request`; they arrive as the `$params`
array argument of the handler.

```php
$router->post("/login", function (Request $request, array $params) {
    $body  = $request->json();               // JSON body
    $name  = $request->post("name");         // form field
    $sort  = $request->get("sort");          // ?sort=name
    $token = $request->header("Authorization");
    $files = $request->file("avatar");
});
```

`file()` normalizes `$_FILES[$field]` into a list of entries
(`$files[$i]["name"|"type"|"size"|"tmp_name"|"error"]`) and is intended for
**multiple** uploads (`name="avatar[]"`). For a single upload, read
`$_FILES[$field]` directly.

### Notes

- `get()`, `post()` and `header()` do **not** apply defaults — a missing key
  returns `null` (`get`/`post` use the null-coalescing operator). Validate first.
- `header()` matches the exact header case sent by the client.

---

## 7. Repository & `DB::query`

### 7.1 Base repository

`Repository` (`app/core/Repository.php`) is a static CRUD base class configured
with three static properties: `$table` (default `"example"`), `$idColumn`
(default `"id"`) and `$fillable` (columns allowed for insert/update).

### 7.2 Available methods

All methods are **static** and use late static binding.

| Method | Signature | Returns | Description |
|---|---|---|---|
| `all` | `all(): array` | `array` | All rows of the table |
| `findOneById` | `findOneById(string $id): mixed` | `array\|false` | Row by primary key (`LIMIT 1`) |
| `find` | `find(array $data): array` | `array` | Rows matching **all** conditions (AND) |
| `findOne` | `findOne(array $data): array` | `array\|false` | First row matching the conditions |
| `insert` | `insert(array $data): PDOStatement` | statement | Insert one row; columns come from the **keys** of `$data` (must be in `$fillable`) |
| `update` | `update(string $id, array $data): PDOStatement` | statement | Update row by primary key; only the given (fillable) columns are set |
| `delete` | `delete(string $id): PDOStatement` | statement | Delete row by primary key |

### 7.3 Usage

```php
UserRepository::all();                            // SELECT * FROM users
UserRepository::findOneById("42");                // SELECT * FROM users WHERE id=? LIMIT 1

UserRepository::findOne(["email" => "a@b.c"]);    // exact match, LIMIT 1
UserRepository::find(["role" => "admin", "active" => 1]);

// Operators: value is [operator, operand]
UserRepository::find(["age" => [">", 18]]);       // WHERE age > ?
UserRepository::find(["name" => ["LIKE", "%john%"]]);
```

Conditions are always combined with `AND`.

### 7.4 Insert / update constraints

Columns are built **from the keys of `$data`** and must be members of
`$fillable`; any other key throws an `ErrorException`
(`Invalid column <name> while insert.` / `... while update.`). Always use
associative arrays.

```php
UserRepository::insert(["name" => "Budi", "email" => "budi@mail.com", "password" => "secret"]);
UserRepository::insert(["name" => "Budi"]);      // partial insert is allowed
UserRepository::update("42", ["name" => "Budi Santoso"]); // partial update
UserRepository::delete("42");
```

### 7.5 Customizing a repository

```php
class UserRepository extends Repository {
    protected static string $table    = "users";
    protected static string $idColumn = "id";
    protected static array  $fillable = ["name", "email", "password"];

    public static function findActiveAdmins(): array {
        return DB::query(
            "SELECT id, name, email FROM users WHERE role = ? AND active = 1 ORDER BY name",
            ["admin"]
        )->fetchAll();
    }
}
```

### 7.6 `DB::query` directly

`DB::query(string $sql = "", array $parameter = []): PDOStatement` prepares and
executes SQL with bound parameters. **Always use placeholders — never interpolate
user input into SQL.**

```php
$rows = DB::query("SELECT * FROM users WHERE age > ? AND city = ?", [18, "Bandung"])->fetchAll();
$row  = DB::query("SELECT * FROM users WHERE id = :id LIMIT 1", ["id" => 42])->fetch();
$newId = DB::query("INSERT INTO users (name) VALUES (?)", ["Sari"])->lastInsertId();
$affected = DB::query("UPDATE users SET name=? WHERE id=?", ["Sari", 1])->rowCount();
```

There is **no fluent query builder** — `DB::query` + raw SQL (or the base
repository) is the data-access API.

---

## 8. Validator

`Validator` (`app/core/Validator.php`) validates data against **rule => message**
maps. The message is returned when the rule fails.

| Method | Input | Success | Failure |
|---|---|---|---|
| `Validator::validate($data, $rules)` | one value | `null` | `string` (first failing message) |
| `Validator::validateAll($data, $rules)` | array of values + rules per field | `null` | `array` of `field => message` |
| `Validator::fromHTTPRequest($rules)` | reads `$_REQUEST` / `$_FILES` | `null` | `array` of `field => message` |

### Rules

| Rule | Validates |
|---|---|
| `required` | value present and not empty/blank |
| `string` / `int` / `float` | strict PHP type |
| `email` | valid e-mail address |
| `min:N` / `max:N` | number, or string length, >= / <= N |
| `enum:a,b,c` | string contained in the list |
| `pattern:REGEX` | string matches the regex (without delimiters) |
| `image` / `pdf` | file MIME type starts with `image/` / is `application/pdf` |
| `mime:type1,type2` | file MIME type is in the list |
| `min_size:N` / `max_size:N` | file size >= / <= N bytes |
| `min_files:N` / `max_files:N` | number of uploaded files |

**Behavior notes**

- If a value is empty/`null` and `required` is **not** among its rules, the field
  passes automatically.
- Rules are checked in order; the first failure wins.
- `int`/`float` check the *PHP type*. HTTP input is always a **string**, so prefer
  `required` + `pattern:\d+` for request data.

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

`validateAll()` applies the same idea to an arbitrary array:

```php
$errors = Validator::validateAll($input, [
    "username" => ["required" => "Username required!", "min:4" => "Too short!"],
    "password" => ["required" => "Password required!", "min:8" => "Min 8 characters!"],
]);
```

---

## 9. Standardized Response

`Response` (`app/core/Response.php`) is the **single way to answer a request**.
`json()` and `redirect()` emit the body and `exit`; `render()` outputs the view
and returns normally. Call `json()`/`redirect()` as the last statement of a
handler.

| Method | Signature | Behavior |
|---|---|---|
| `status` | `Response::status(int $status)` | Set the HTTP status code |
| `json` | `Response::json(array $data, int $status = 200)` | `Content-Type: application/json`, echoes `json_encode($data)` |
| `redirect` | `Response::redirect(string $dest, int $status = 302)` | Sets `Location: $dest` |
| `render` | `Response::render(string $view, array $data = [], int $status = 200)` | Renders `app/views/<view>.view.php` |

### Recommended JSON envelope

```php
Response::json(["status" => "success", "data" => $payload], 200);   // or 201 on create
Response::json(["status" => "error", "message" => "Something went wrong"], 500);
Response::json(["status" => "error", "errors" => $errors], 422);     // Validator output
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
| `404` | Resource/route not found |
| `422` | Validation errors |
| `500` | Server error |

```php
Response::redirect(BASE_URL . "/login");   // BASE_URL keeps subdirectory installs working
```

---

## 10. Complete Example

End-to-end "create user" feature:

```php
// app/repositories/UserRepository.php
<?php declare(strict_types=1);
class UserRepository extends Repository {
    protected static string $table    = "users";
    protected static string $idColumn = "id";
    protected static array  $fillable = ["name", "email", "password"];
}

// app/services/UserService.php
<?php declare(strict_types=1);
class UserService extends Service {
    public function __construct() {
        $this->repository = new UserRepository();
    }

    public function create(array $input): string {
        return UserRepository::insert([
            "name"     => trim($input["name"]),
            "email"    => strtolower(trim($input["email"])),
            "password" => password_hash($input["password"], PASSWORD_DEFAULT),
        ])->lastInsertId();
    }
}

// app/controllers/UserController.php
<?php declare(strict_types=1);
class UserController extends Controller {
    public function __construct() {
        $this->service = new UserService();
    }

    public function index(Request $request, array $params): void {
        Response::json(["status" => "success", "data" => UserRepository::all()]);
    }

    public function store(Request $request, array $params): void {
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
require_once __DIR__ . "/app/repositories/UserRepository.php";
require_once __DIR__ . "/app/services/UserService.php";

$router = new Router();
$router->get("/users",  ["UserController", "index"]);
$router->post("/users", ["UserController", "store"]);
$router->run();
```

---

## 11. Gotchas & Notes

1. **No autoloader** — repositories/services must be `require_once`d in
   `index.php`; controllers are loaded by the router when referenced by name.
2. **Handler signature is `(Request $request, array $params)`** — including
   controller methods referenced as `[ClassName, "method"]` (no constructor args).
3. **No middleware / route groups** — cross-cutting checks (auth, CORS) are done
   with extra handlers or inside the front controller.
4. **Route order matters** — first registered match wins; declare static paths
   before `:param` paths. Unregistered methods yield a 404.
5. **`insert()`/`update()` must be associative** — columns come from array keys;
   every key must be listed in `$fillable`, otherwise an `ErrorException` is
   thrown. `update()` supports partial updates.
6. **HTTP input is strings** — strict `int`/`float` rules fail on request data;
   use `pattern:\d+` then cast.
7. **`json()`/`redirect()` exit** — they echo and terminate; call them last.
   `render()` outputs the view and returns normally.
8. **Upload directories must exist** — `Storage::save()`/`put()` fail if the
   target directory was not created beforehand.
9. **`app/` is not web-accessible** — keep public assets/uploads outside it.
10. **Schema management is manual** — no migrations, seeds, or CLI tooling.
11. **Fail-fast database** — a connection error `die()`s with the PDO message;
    never expose that in production.
