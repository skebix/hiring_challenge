<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 10:02 AM
 */

use app\middleware\ConfigMiddleware;
use app\middleware\CookieMiddleware;
use app\middleware\CorsMiddleware;
use Dotenv\Dotenv;
use Slim\Container;
use Slim\Http\Cookies;

$container['cookie'] = function (Container $container) {
    $request = $container->request;
    return new Cookies($request->getCookieParams());
};

$container['dotenv'] = function (Container $container) {
    $settings = $container->settings['dotenv'];
    return new Dotenv($settings['dotenv_path']);
};

$container['app\middleware\ConfigMiddleware'] = function (Container $container) {
    $dotenv = $container->get('dotenv');
    return new ConfigMiddleware($dotenv);
};

$container['app\middleware\CorsMiddleware'] = function (Container $container) {
    $dotenv = $container->get('dotenv');
    return new CorsMiddleware($dotenv);
};

$container['app\middleware\CookieMiddleware'] = function (Container $container) {
    $cookie = $container->get('cookie');
    return new CookieMiddleware($cookie);
};