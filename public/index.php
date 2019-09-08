<?php

declare(strict_types=1);

use App\ContainerFactory;
use App\Controllers\AdminIndexController;
use App\Controllers\AppCategoryController;
use App\Controllers\AppController;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\twig\TwigExtension;
use Medoo\Medoo;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

// Set the default timezone.
date_default_timezone_set('Europe/Zurich');

// Set the absolute path to the root directory.
$rootPath = realpath(__DIR__ . '/..');

// Include the composer autoloader.
include_once($rootPath . '/vendor/autoload.php');

// Create the container for dependency injection.
try {
    $container = ContainerFactory::create($rootPath);
} catch (Exception $e) {
    die($e->getMessage());
}

// Set the container to create the App with AppFactory.
AppFactory::setContainer($container);
$app = AppFactory::create();

$container = $app->getContainer();

// Set the cache file for the routes. Note that you have to delete this file
// whenever you change the routes.
//$app->getRouteCollector()->setCacheFile(
//    $rootPath . '/cache/routes.cache'
//);

// Add the routing middleware.
$app->addRoutingMiddleware();

// Add the twig middleware (which when processed would set the 'view' to the container).

$twig = new Twig(
    $rootPath . '/application/templates',
    [
//        'cache' => $rootPath . '/cache',
        'auto_reload' => true,
        'debug' => true,
    ]
);
$twig->addExtension(new TwigExtension());

$app->add(
    new TwigMiddleware(
        $twig,
        $container,
        $app->getRouteCollector()->getRouteParser(),
        $app->getBasePath()
    )
);

// Add error handling middleware.
$displayErrorDetails = true;
$logErrors = true;
$logErrorDetails = false;
$app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);

// Define the app routes.
$app->group('/', function (RouteCollectorProxy $group) {
    $group->get('', HomeController::class)->setName('home');
    $group->get('login', LoginController::class)->setName('login');
    $group->get('category/{category_slug}', AppCategoryController::class)->setName('category');
    $group->get('category/{category_slug}/', AppCategoryController::class)->setName('category');
    $group->get('admin/apps', AdminIndexController::class)->setName('admin_apps');
    $group->get('{slug_url}/{package_name}', AppController::class)->setName('app');
    $group->get('{slug_url}/{package_name}/', AppController::class)->setName('app');
});

// Run the app.
$app->run();
