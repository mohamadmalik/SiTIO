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

// Dashboard Utama
$router->get("/", function() {
    Response::render("Dashboard.index", ["page" => "dashboard"]);
});

$router->get("/sample", ["SampleController", "test"]);

$renderPdln = function (Request $request, array $params) {
    if (session_status() === PHP_SESSION_NONE && !session_start()) {
        throw new RuntimeException("Gagal memulai sesi untuk halaman PDLN.");
    }

    Response::render("Dashboard.index", ["page" => "pdln"]);
};

$router->get("/daftar-pdln", $renderPdln);
$router->post("/daftar-pdln", $renderPdln);
$router->get("/daftar-pdln.php", $renderPdln);
$router->post("/daftar-pdln.php", $renderPdln);

$router->get("/:param_1", function(Request $request, array $params) {
    Response::render("Dashboard.index", ["params" => $params]);
});

$router->run();
