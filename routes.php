<?php declare(strict_types=1);

$router->get("/", fn() => Response::redirect(BASE_URL . "/adb"), fn() => Response::render("tes"));
$router->get("/sample", ["SampleController", "test"]);
$router->get("/:param_1", function(Request $request, array $params) {
    Response::render("tes", ["params" => $params]);
});
