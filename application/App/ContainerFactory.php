<?php

declare(strict_types=1);

namespace App;

use App\utils\GooglePlayCategory;
use DI\ContainerBuilder;
use Exception;
use Medoo\Medoo;
use PDO;
use Psr\Container\ContainerInterface;

class ContainerFactory
{
    /**
     * @param string $rootPath
     *
     * @return ContainerInterface
     * @throws Exception
     */
    public static function create(string $rootPath): ContainerInterface
    {
        $db = new Medoo([
            // required
            'database_type' => 'mysql',
            'database_driver' => 'pdo_mysql',
            'database_name' => 'apk',
            'server' => 'localhost',
            'username' => 'quannt',
            'password' => 'VuYen2503',
            //optional
            'charset' => 'utf8',
            'port' => 3306,
        ]);
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            Preferences::class => new Preferences($rootPath),
            Medoo::class => $db,
            GooglePlayCategory::class => new GooglePlayCategory($db)
        ]);
        $containerBuilder->addDefinitions($rootPath . '/application/config/container-definitions.php');
        $containerBuilder->addDefinitions($rootPath . '/application/config/container-controllers.php');

        // Note: In production, you should enable container-compilation.
        // $containerBuilder->enableCompilation($rootPath . '/cache');

        return $containerBuilder->build();
    }
}
