<?php declare(strict_types=1);

require_once __DIR__ . "/app/core/Log.php";
require_once __DIR__ . "/app/core/Env.php";
require_once __DIR__ . "/app/core/Database.php";
require_once __DIR__ . "/app/core/Storage.php";
require_once __DIR__ . "/app/core/Validator.php";
require_once __DIR__ . "/app/core/Repository.php";
require_once __DIR__ . "/app/core/Service.php";
require_once __DIR__ . "/app/core/Controller.php";
require_once __DIR__ . "/app/core/Request.php";
require_once __DIR__ . "/app/core/Response.php";
require_once __DIR__ . "/app/core/Router.php";

Env::load(__DIR__ . "/.env");

$router = new Router();
require_once __DIR__ . "/routes.php";
$router->run();
