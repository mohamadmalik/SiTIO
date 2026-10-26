<?php declare(strict_types=1);

$router->get("/", fn() => Response::render("Dashboard.index", ["page" => "dashboard"]));
$router->get("/daftar-outbound", fn () => Response::render("Dashboard.index", ["page" => "outbound"]));
$router->post("/daftar-outbound", fn () => Response::render("Dashboard.index", ["page" => "outbound"]));
$router->get("/daftar-inbound", fn () => Response::render("Dashboard.index", ["page" => "inbound"]));
$router->post("/daftar-inbound", fn () => Response::render("Dashboard.index", ["page" => "inbound"]));
$router->get("/daftar-tiket", fn () => Response::render("Dashboard.index", ["page" => "tiket"]));
$router->post("/daftar-tiket", fn () => Response::render("Dashboard.index", ["page" => "tiket"]));
$router->get("/daftar-pdln", fn () => Response::render("Dashboard.index", ["page" => "pdln"]));
$router->post("/daftar-pdln", fn () => Response::render("Dashboard.index", ["page" => "pdln"]));
