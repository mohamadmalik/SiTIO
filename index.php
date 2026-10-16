<?php

require_once __DIR__ . "/app/core/database.php";
require_once __DIR__ . "/app/core/storage.php";
require_once __DIR__ . "/app/core/validator.php";
require_once __DIR__ . "/app/core/repository.php";
require_once __DIR__ . "/app/core/service.php";
require_once __DIR__ . "/app/core/controller.php";
require_once __DIR__ . "/app/core/router.php";

$router = new Router();
$router->get("/:a/:b/:c", function() {
    var_dump($_SERVER["URL_PARAM"]);
});
$router->run();
