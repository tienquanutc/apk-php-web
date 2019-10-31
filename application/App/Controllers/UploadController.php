<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Views\Twig;

class UploadController extends AbstractTwigController {
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
     */
    public function __construct(Twig $twig, Preferences $preferences) {
        parent::__construct($twig);

        $this->preferences = $preferences;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args = []): Response {
        $directory = $this->preferences->getRootPath().DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."files";
        $body = $request->getBody();

        $headerFileName = $request->getHeader('X-File-Name')[0];
        $headerFileSize = $request->getHeader('X-File-Size')[0];
        $extension = pathinfo($headerFileName, PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $fileName = sprintf('%s.%0.8s', $basename, $extension);

        file_put_contents($directory . DIRECTORY_SEPARATOR .$fileName, $body, FILE_BINARY | LOCK_EX);

        echo json_encode([
            'file_name' => $fileName,
            'file_size' => $headerFileSize,
            'file_url' => '/files/'.$fileName,
        ]);
        $response = $response->withHeader('Content-Type', "application/json");
        return $response;
    }
}
