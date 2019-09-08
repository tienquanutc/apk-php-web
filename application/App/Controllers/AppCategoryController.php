<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use App\utils\GooglePlayCategory;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AppCategoryController extends AbstractTwigController {
    /**
     * @var Preferences
     */
    private $preferences;
    private $database;
    private $googlePlayCategory;

     const PAGE_SIZE = 24;

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
        $page = $request->getQueryParams()['page'] ?: 1;
        $categorySlug = $args['category_slug'];
        $category =  $this->googlePlayCategory->getCategoryBySlug($categorySlug);

        $where = $category['category_id'] == 'GAME' || $category['category_id'] == 'APPLICATION' ? ['category_type_id' => $category['category_id']] : ['app.category_id' => $category['category_id']];
        $where['LIMIT'] = [($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE];
        $where['ORDER'] = ["num_downloads"=>"DESC"];

        $apps = $this->database->select('app',
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
            ],
                $where
        );

        unset($where['LIMIT']);
        $count = $this->database->count('app', $where);

        $pagination = [
            "path" => $request->getUri()->getPath(),
            "paging_query" => preg_replace('page=\d+&?', '', $request->getUri()->getQuery()),
            "current_page_index" => $page,
            "total_pages" => ceil($count/self::PAGE_SIZE)
        ];

        return $this->render($response, 'app_category.twig', [
            'apps' => $apps,
            'breadcrumb_items' => $this->buildBreadcrumbItems($category),
            'category' => $category,
            'app_categories' => $this->googlePlayCategory->appCategories,
            'game_categories' => $this->googlePlayCategory->gameCategories,
            'pagination' => $pagination,
            'rootPath' => $this->preferences->getRootPath()]);
    }


    private function buildBreadcrumbItems($category) {
        if ($category['category_id'] == 'GAME' || $category['category_id'] == 'APPLICATION') {
            return [
                ['text' => 'Danh mục', 'href'=>'/category/'],
                ['text' => $category['category_name']],
            ];
        }
        return [
            ['text' => 'Danh mục', 'href'=>'/category/'],
            ['text' => $category['category_type_name'], 'href'=>"/category/${category['category_type_slug']}"],
            ['text' => $category['category_name'], 'href'=>"/category/${category['category_type_name']}"],
        ];
    }
}
