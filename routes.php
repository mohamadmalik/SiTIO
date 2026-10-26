<?php declare(strict_types=1);

// Render view 'klasemen' (otomatis memanggil app/views/klasemen.view.php)
$router->get("/", fn() => Response::render("Dashboard.index"));

$router->get("/sample", ["SampleController", "test"]);

$router->get("/:param_1", function(Request $request, array $params) {
    Response::render("Dashboard.index", ["params" => $params]);
});
