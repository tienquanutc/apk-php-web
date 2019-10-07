<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use App\utils\GooglePlayCategory;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AppEditController extends AbstractTwigController {
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
        $app = $this->database->select('app','*', ["package_name" => $package_name])[0];
        $category = $this->googlePlayCategory->getCategoryById($app['category_id']);

        //GET
        if($request->getMethod() =='GET') {
            return $this->render($response, 'app_edit.twig', [
                'app' => $app,
                'category' => $category,
                'action' => strtoupper($args['action']),
                'app_categories' => $this->googlePlayCategory->appCategories,
                'game_categories' => $this->googlePlayCategory->gameCategories,
                'rootPath' => $this->preferences->getRootPath(),
                'breadcrumb_items' => $this->buildBreadcrumbItems($app, $category),
            ]);
         }
        //POST

        $formAttributes = $request->getParsedBody();
        $action = $formAttributes['action'] ?: 'NEW';
        $fields = ['package_name', 'title', 'version_string', 'version_code', 'updated', 'installation_size',
            'num_downloads', 'category_id', 'description_short', 'description_html', 'recent_changes_html',
            'icon_url', 'developer_id'];

        $update = array_reduce($fields, function ($map, $f) use ($formAttributes) {
            $map[$f] = $formAttributes[$f];
            return $map;
        }, []);
        $update['slug_url'] = AppEditController::slugify($update['title']);


        switch ($action) {
            case 'UPDATE':
                $data = $this->database->update('app', $update, ['package_name' => $package_name]);
                if($data->rowCount() > 0) $package_name = $update['package_name'] ?: $package_name;
                $response = $response->withStatus(302);
                $response = $response->withHeader('location', "/app/$package_name/update");
                break;
            case 'DELETE';
                $this->database->delete('app', ['package_name' => $package_name]);
                echo "DELETE OK";
                break;
            case 'NEW';
                $data = $this->database->insert('app', $update);
                if($data->rowCount() > 0) $package_name = $update['package_name'];
                $response = $response->withStatus(302);
                $response = $response->withHeader('location', "/app/$package_name");
                break;
        }

        return $response;

    }

    private function buildBreadcrumbItems($app, $category) {

        return [
            ['text' => $category['category_type_name'], 'href'=>"/category/${category['category_type_slug']}"],
            ['text' => $category['category_name'], 'href'=>"/category/${category['category_slug']}"],
            ['text' => $app['title'], 'href'=>"/${app['slug_url']}/${app['package_name']}/"],
            ['text' => "Chỉnh sửa"]
        ];
    }

    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
