<?php

declare(strict_types=1);

use App\Controller\CharacterController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\ViewRenderer;
use App\Infrastructure\FileCache;
use App\Infrastructure\RickAndMortyApiClient;
use App\Service\CharacterService;
use function App\Support\app_path;

$autoload = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (! is_file($autoload)) {
    http_response_code(500);
    echo 'Dependencies are missing. Please run "composer install" first.';
    exit;
}

require $autoload;

$cacheTtl = max(60, (int) (getenv('API_CACHE_TTL') ?: 300));
$apiBaseUri = (string) (getenv('RICK_AND_MORTY_API_BASE_URI') ?: 'https://rickandmortyapi.com/api');

$views = new ViewRenderer(app_path('resources/views'));
$client = new RickAndMortyApiClient($apiBaseUri, new FileCache(app_path('storage/cache'), $cacheTtl));
$controller = new CharacterController(new CharacterService($client), $views);

$router = new Router();
$router->get('/', fn (Request $request, array $parameters): Response => $controller->index($request));
$router->get('/characters', fn (Request $request, array $parameters): Response => $controller->index($request));
$router->get('/characters/{id}', fn (Request $request, array $parameters): Response => $controller->show($request, $parameters));

$router->dispatch(Request::fromGlobals())->send();
