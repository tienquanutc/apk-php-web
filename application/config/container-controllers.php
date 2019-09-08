<?php

declare(strict_types=1);

use App\Controllers\AdminIndexController;
use App\Controllers\AppCategoryController;
use App\Controllers\AppController;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Preferences;
use App\utils\GooglePlayCategory;
use Medoo\Medoo;
use Psr\Container\ContainerInterface;

return [
    HomeController::class => function (ContainerInterface $container): HomeController {
        return new HomeController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class),$container->get(GooglePlayCategory::class));
    },
    AppCategoryController::class => function (ContainerInterface $container): AppCategoryController {
        return new AppCategoryController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class), $container->get(GooglePlayCategory::class));
    },
    AppController::class => function (ContainerInterface $container): AppController {
        return new AppController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class), $container->get(GooglePlayCategory::class));
    },
    LoginController::class => function (ContainerInterface $container): LoginController {
        return new LoginController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class));
    },
    AdminIndexController::class => function (ContainerInterface $container): AdminIndexController {
        return new AdminIndexController($container->get('view'), $container->get(Preferences::class), $container->get(Medoo::class), $container->get(GooglePlayCategory::class));
    },
];
