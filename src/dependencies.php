<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 10:02 AM
 */

use app\middleware\ConfigMiddleware;
use app\middleware\CorsMiddleware;
use Dotenv\Dotenv;
use Slim\Container;

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