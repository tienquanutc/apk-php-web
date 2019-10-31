<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use App\utils\GooglePlayCategory;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class CategoryController extends AbstractTwigController {
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
        return $this->render($response, 'category.twig', [
            'categories' => $this->googlePlayCategory->categories,
            'app_categories' => $this->googlePlayCategory->appCategories,
            'game_categories' => $this->googlePlayCategory->gameCategories,
            'breadcrumb_items' => $this->buildBreadcrumbItems(),
            'rootPath' => $this->preferences->getRootPath()]);
    }

    private function buildBreadcrumbItems() {
        return [
            ['text' => 'Danh mục']
        ];
    }
}
