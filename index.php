<?php declare(strict_types=1);

require_once __DIR__ . "/app/core/Database.php";
require_once __DIR__ . "/app/core/Storage.php";
require_once __DIR__ . "/app/core/Validator.php";
require_once __DIR__ . "/app/core/Repository.php";
require_once __DIR__ . "/app/core/Service.php";
require_once __DIR__ . "/app/core/Controller.php";
require_once __DIR__ . "/app/core/Request.php";
require_once __DIR__ . "/app/core/Response.php";
require_once __DIR__ . "/app/core/Router.php";

$router = new Router();

// Render view 'klasemen' (otomatis memanggil app/views/klasemen.view.php)
$router->get("/", function() {
    Response::render("Dashboard.index");
});

$router->get("/sample", ["SampleController", "test"]);

$router->get("/:param_1", function(Request $request, array $params) {
    Response::render("Dashboard.index", ["params" => $params]);
});

$router->run();