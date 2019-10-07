<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use App\utils\GooglePlayCategory;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AppSearchController extends AbstractTwigController {
    /**
     * @var Preferences
     */
    private $preferences;
    private $database;
    private $googlePlayCategory;

    const MAX_SIZE = 24;

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
        $query = $request->getQueryParams()['q'];
        $apps = $this->database->select("app",
            ["[><]category"=>"category_id"],
            [
                "app.package_name",
                "app.num_downloads",
                "app.slug_url",
                "app.installation_size",
                "app.title",
                "app.icon_url",
                "app.category_id",
                "app.developer_id",
                "category.category_type_id"
            ], [
            "MATCH" => [
                "columns" => ["title", "description_short", "package_name"],
                "keyword" => "$query",
                "mode" => "natural"
            ],
            'LIMIT' => self::MAX_SIZE
        ]);

        $alternateApps = [];
        if (!$apps) {
            $alternateApps = $this->database->select("app",
                ["[><]category"=>"category_id"],
                [
                    "app.package_name",
                    "app.num_downloads",
                    "app.slug_url",
                    "app.installation_size",
                    "app.title",
                    "app.icon_url",
                    "app.category_id",
                    "app.developer_id",
                    "category.category_type_id"
                ], [
                    'LIMIT' => 12,
                    "ORDER" => Medoo::raw('RAND()')
                ]);
        }

        return $this->render($response, 'app_search.twig', [
            'apps' => $apps,
            'alternate_apps' => $alternateApps,
            'query' => $query,
            'app_categories' => $this->googlePlayCategory->appCategories,
            'game_categories' => $this->googlePlayCategory->gameCategories,
        ]);
    }
}
