<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use App\utils\GooglePlayCategory;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AdminIndexController extends AbstractTwigController {
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
        $orderField =  $request->getQueryParams()['sort'] ?: "num_downloads";
        $categoryId = $request->getQueryParams()['cat_id'];
        $page = $request->getQueryParams()['page'] ?: 1;

        if($categoryId) {
            $where = $categoryId == 'GAME' || $categoryId == 'APPLICATION' ? ['category_type_id' => $categoryId] : ['app.category_id' => $categoryId];
        }

        $where['LIMIT'] = [($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE];
        $where['ORDER'] = [$orderField=>"DESC"];

        $join = ["[><]category" => "category_id"];
        $column = [
            "app.package_name",
            "app.num_downloads",
            "app.slug_url",
            "app.installation_size",
            "app.title",
            "app.icon_url",
            "app.category_id",
            "app.developer_id",
            "category.category_type_id"
        ];

        $apps = $this->database->select('app',$join, $column, $where);

        unset($where['LIMIT']);
        unset($where['ORDER']);
        $count = $this->database->count('app' ,$join, "*", $where);

        $pagingQuery = preg_replace('/page=\d+&?/', '', $request->getUri()->getQuery());
        $categoryQuery = preg_replace('/&?cat_id=(.*&|.*$)/', '', $pagingQuery);
        $sortQuery = preg_replace('/&?sort=(.*&|.*$)/', '', $pagingQuery);

        $pagination = [
            "path" => $request->getUri()->getPath(),
            "paging_query" => preg_replace('/page=\d+&?/', '', $request->getUri()->getQuery()),
            "current_page_index" => $page,
            "total_pages" => ceil($count/self::PAGE_SIZE)
        ];

        return $this->render($response, 'admin_index.twig', [
            'apps' => $apps,
            'pagination' => $pagination,
            'app_categories' => $this->googlePlayCategory->appCategories,
            'game_categories' => $this->googlePlayCategory->gameCategories,
            'category_query' => $categoryQuery,
            'sort_query' => $sortQuery,
            'rootPath' => $this->preferences->getRootPath(),
            ]);
    }
}
