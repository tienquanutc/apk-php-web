<?php

declare(strict_types=1);

use App\Controllers\AppCategoryController;
use App\Controllers\AppController;
use App\Controllers\AppEditController;
use App\Controllers\AppSearchController;
use App\Controllers\CategoryController;
use App\Controllers\HomeController;
use App\Controllers\UploadController;
use App\Preferences;
use App\utils\GooglePlayCategory;
use Medoo\Medoo;
use Psr\Container\ContainerInterface;
use Slim\Flash\Messages;

return [
    HomeController::class => function (ContainerInterface $container): HomeController {
        return new HomeController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class),$container->get(GooglePlayCategory::class));
    },
    UploadController::class => function (ContainerInterface $container): UploadController {
        return new UploadController($container->get('view'), $container->get(Preferences::class));
    },
    AppCategoryController::class => function (ContainerInterface $container): AppCategoryController {
        return new AppCategoryController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class), $container->get(GooglePlayCategory::class));
    },
    AppController::class => function (ContainerInterface $container): AppController {
        return new AppController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class), $container->get(GooglePlayCategory::class));
    },
    AppEditController::class => function (ContainerInterface $container): AppEditController {
        return new AppEditController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class), $container->get(GooglePlayCategory::class));
    },
    AppSearchController::class => function (ContainerInterface $container): AppSearchController {
        return new AppSearchController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class), $container->get(GooglePlayCategory::class));
    },
    CategoryController::class => function (ContainerInterface $container): CategoryController {
        return new CategoryController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class), $container->get(GooglePlayCategory::class));
    },
];
