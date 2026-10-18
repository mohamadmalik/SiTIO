# PHP Boilerplate

A minimal, **zero-dependency** PHP boilerplate for building small web APIs and
web applications. It ships a tiny hand-rolled core — router, PDO wrapper,
repository/service/controller layering, request/response helpers, validator and
filesystem utilities — with no Composer packages, no framework, and no build step.

Clone it, point it at a database, and start adding routes.

```php
$router = new Router();
$router->get("/ping", fn () => Response::json(["status" => "success", "data" => ["pong" => true]]));
$router->run();
```

---

## Features

- **Router** — `GET`/`POST` routes with `:param` placeholders, subdirectory-aware, automatic 404
- **Database** — lazy PDO singleton with `DB::query()` prepared statements and transactions
- **Repository pattern** — static base `Repository` (`findAll`, `find`, `findOne`, `findOneById`, `insert`, `update`, `delete`) configured via `$table` / `$idColumn` / `$fillable`
- **Service & Controller layers** — skeletons enforcing `Controller → Service → Repository`
- **Request / Response** — typed access to params, query, body, headers and uploads; standardized `Response::json()` and `Response::redirect()`
- **Validator** — rule/message maps for strings, numbers, emails, enums, patterns and file uploads (`image`, `pdf`, `mime:`, size/count rules)
- **Storage** — static file helpers: `exists`, `get`, `put`, `save` (uploads), `unlink`
- **No dependencies** — plain PHP >= 8.1, `declare(strict_types=1)` everywhere, global namespace

## Requirements

- PHP **>= 8.1** with PDO (MySQL/MariaDB by default)
- Apache with `mod_rewrite` (`.htaccess` included), or the PHP built-in server for development
- A database (schema is managed manually — no migrations are included)

## Quick Start

```bash
# HTTPS
git clone https://github.com/navetacandra/php_template.git my-app

# or SSH
git clone git@github.com:navetacandra/php_template.git my-app

cd my-app
```

1. Create your database and tables (any SQL client).
2. Configure the connection via environment variables — `DB_DRIVER`, `DB_HOST`,
   `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` (e.g. with `SetEnv` in Apache, or
   inline when running `php -S localhost:8000 index.php`). Defaults assume
   `root`/`root` on `localhost:3306`.
3. Serve the project root (Apache vhost / docroot, or the built-in server).
4. Add your routes in `index.php` and your classes under `app/`.

Full instructions: **[DOCS.md](DOCS.md)**.

## Project Structure

```
.
├── index.php                 # Front controller: requires, routes, $router->run()
├── .htaccess                 # Rewrite all non-file requests to index.php
└── app/
    ├── .htaccess             # Deny from all (internal code is not web-accessible)
    ├── controllers/          # Controllers (HTTP layer)
    ├── services/             # Services (business logic)
    ├── repositories/         # Repositories (data access)
    └── core/                 # Framework core
        ├── database.php      # DB — PDO wrapper, DB::query, transactions
        ├── repository.php    # Repository — static CRUD base
        ├── service.php       # Service — base service
        ├── controller.php    # Controller — base controller
        ├── request.php       # Request — route params, GET/POST, headers, files
        ├── response.php      # Response — json() / redirect()
        ├── router.php        # Router — get(), post(), run()
        ├── storage.php       # Storage — file helpers
        └── validator.php     # Validator — validation rules
```

Architecture: **Route → Controller → Service → Repository → DB (PDO)**.

## Documentation

| Document | Content |
|---|---|
| [DOCS.md](DOCS.md) | How to use the boilerplate: database setup, adding repositories/controllers/services, routes, `Storage::*`, `Request`, `Repository` & `DB::query`, `Validator`, standardized `Response` |
| README.md | Project overview (this file) |

## License

Released under the [MIT License](LICENSE).
