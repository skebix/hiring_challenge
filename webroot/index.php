<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 11:22 AM
 */

use Slim\App;
use Slim\Container;

require __DIR__ . '/../vendor/autoload.php';

// Set up container with settings array
$settings = require __DIR__ . '/../src/settings.php';
$container = new Container($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Instantiate the app
$app = new App($container);

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
