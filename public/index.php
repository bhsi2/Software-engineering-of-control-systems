<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Загружаем переменные окружения
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Создаём приложение
$app = AppFactory::create();

// Добавляем middleware для обработки ошибок (по желанию)
$app->addErrorMiddleware(true, true, true);

// Подключаем маршруты
$app->get('/stats/{telegramId}', \App\Controller\StatsController::class . ':getStats');

//$pdo = new PDO($_ENV['DB_DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
$userRepo = new class extends App\Repository\UserRepository {
    public function __construct() {}
    public function findSteamIdByTelegramId(int $telegramId): ?string {
        return '76561197960287930'; // тестовый SteamID
    }
};
$steamApi = new App\Service\SteamApiService($_ENV['STEAM_API_KEY']);
$statsProvider = new App\Service\SteamStatsProvider($steamApi);

$app->get('/stats/{telegramId}', function ($request, $response, $args) use ($userRepo, $statsProvider) {
    $controller = new App\Controller\StatsController($userRepo, $statsProvider);
    return $controller->getStats($request, $response, $args);
});
$app->run();