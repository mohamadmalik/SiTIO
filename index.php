<?php

require_once __DIR__ . "/app/database/connection.php";
require_once __DIR__ . "/app/validators/validator.php";

$rule = [
    "name" => [
        "required" => "Name is required!",
        "max:4" => "Name max length is 4 char!"
    ],
    "email" => [
        "email" => "Email must be valid mail address!"
    ],
    "file:any" => [
        "required" => "any is required!",
        "min_files:2" => "any file at least 2!",
    ]
];
// var_dump(is_null(null));
var_dump(
    Validator::fromHTTPRequest($rule)
);
