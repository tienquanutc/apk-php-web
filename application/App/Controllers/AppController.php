<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use App\utils\GooglePlayCategory;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AppController extends AbstractTwigController {
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
        $package_name = $args['package_name'] ;
        $app = $this->database->select('app', '*' ,['package_name'=>$package_name])[0];
        $category = $this->googlePlayCategory->getCategoryById($app['category_id']);

        $sameCategoryApps = $this->database->select('app', '*', ['category_id'=>$category['category_id'], "LIMIT" => 5, "ORDER" => Medoo::raw('RAND()')]);

        if(!$app){
            return $this->render($response, '404.twig');
        }
        return $this->render($response, 'app.twig', [
            'app' => $app,
            'same_category_apps' => $sameCategoryApps,
            'breadcrumb_items' => $this->buildBreadcrumbItems($app, $category),
            'category' => $category,
            'categories' => $this->googlePlayCategory->categories,
            'app_categories' => $this->googlePlayCategory->appCategories,
            'game_categories' => $this->googlePlayCategory->gameCategories,
            'rootPath' => $this->preferences->getRootPath()]);
    }

    private function buildBreadcrumbItems($app, $category) {
        return [
            ['text' => $category['category_type_name'], 'href'=>"/category/${category['category_type_slug']}"],
            ['text' => $category['category_name'], 'href'=>"/category/${category['category_slug']}"],
            ['text' => $app['title']]
        ];
    }
}
