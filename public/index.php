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

$app->run();