<?php

declare(strict_types=1);

use App\BookRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$repository = new BookRepository(__DIR__ . '/../data/books.json');

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

/**
 * Записує дані у відповідь у форматі JSON.
 */
function json(Response $response, mixed $data, int $status = 200): Response
{
    $response->getBody()->write(
        json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
    );

    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus($status);
}

$app->get('/', function (Request $request, Response $response): Response {
    return json($response, [
        'name' => 'Books API',
        'endpoints' => [
            'GET /api/books' => 'список книг (фільтри: ?genre=..., ?author=...)',
            'GET /api/books/{id}' => 'одна книга за id',
            'GET /api/genres' => 'список жанрів',
        ],
    ]);
});

$app->get('/api/books', function (Request $request, Response $response) use ($repository): Response {
    $params = $request->getQueryParams();
    $genre = isset($params['genre']) && $params['genre'] !== '' ? (string) $params['genre'] : null;
    $author = isset($params['author']) && $params['author'] !== '' ? (string) $params['author'] : null;

    return json($response, $repository->findAll($genre, $author));
});

$app->get('/api/books/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($repository): Response {
    $book = $repository->findById((int) $args['id']);

    if ($book === null) {
        return json($response, ['error' => 'Книгу не знайдено'], 404);
    }

    return json($response, $book);
});

$app->get('/api/genres', function (Request $request, Response $response) use ($repository): Response {
    return json($response, $repository->genres());
});

$app->run();
