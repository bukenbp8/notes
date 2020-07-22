<?php

use Models\Users;
use Core\Cookie;
use Core\Session;
use Core\Router;

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/lib/helpers/helpers.php';


if (!Session::exists(CURRENT_USER_SESSION_NAME) && Cookie::exists(REMEMBER_ME_COOKIE_NAME)) {
    Users::loginUserFromCookie();
}

$router = new Router();
$router->initRouter();
