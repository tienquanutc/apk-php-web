<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use App\utils\GooglePlayCategory;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class HomeController extends AbstractTwigController {
    /**
     * @var Preferences
     */
    private $preferences;
    private $database;
    private $googlePlayCategory;

    /**
     * HomeController constructor.
     *
     * @param Twig $twig
     * @param Preferences $preferences
     * @param Medoo $database
     * @param GooglePlayCategory $googlePlayCategory
     */
    public function __construct(Twig $twig, Preferences $preferences, Medoo $database, GooglePlayCategory $googlePlayCategory) {
        parent::__construct($twig);

        $this->preferences = $preferences;
        $this->database = $database;
        $this->googlePlayCategory = $googlePlayCategory;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args = []): Response {
        $categories = $data = $this->database->select('category', 'category_id');

        $query = join("union all", array_map(function ($category) {
            return "(select * from app where category_id = '$category' order by num_downloads DESC limit 3)";
        }, $categories));
        $apps = $this->database->query($query)->fetchAll();

        $appsByCategory = array_reduce($apps, function ($map, $app) {
            if (!$map[$app['category_id']]) $map[$app['category_id']] = [];
            array_push($map[$app['category_id']], $app);
            return $map;
        }, []);

        return $this->render($response, 'index.twig', [
            'apps_by_category' => $appsByCategory,
            'app_categories' => $this->googlePlayCategory->appCategories,
            'game_categories' => $this->googlePlayCategory->gameCategories,
            'rootPath' => $this->preferences->getRootPath(),
            ]);
    }
}
