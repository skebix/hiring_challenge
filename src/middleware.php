<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 10:02 AM
 */

$app->add(\app\middleware\CookieMiddleware::class);
$app->add(\app\middleware\CorsMiddleware::class);
$app->add(\app\middleware\ConfigMiddleware::class);