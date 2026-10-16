<?php declare(strict_types=1);

require_once __DIR__ . "/app/core/database.php";
require_once __DIR__ . "/app/core/storage.php";
require_once __DIR__ . "/app/core/validator.php";
require_once __DIR__ . "/app/core/repository.php";
require_once __DIR__ . "/app/core/service.php";
require_once __DIR__ . "/app/core/controller.php";
require_once __DIR__ . "/app/core/request.php";
require_once __DIR__ . "/app/core/response.php";
require_once __DIR__ . "/app/core/router.php";

$router = new Router();
$router->get("/", function() {
    var_dump("Home");
});
$router->get("/:abc", function(Request $request) {
    var_dump($request->param("abc"));
});
$router->run();
