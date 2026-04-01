# Steam Stats Microservice

Микросервис на Laravel для получения статистики Steam-аккаунта по Telegram ID.

## Требования

- Docker и Docker Compose (версия 1.27+)

## Установка и запуск

1. **Клонируйте репозиторий**  
   ```
   bash
   git clone https://github.com/your-username/steam-stats-service.git
   cd steam-stats-service
   ```
2. **Скопируйте файл окружения**
```
cp .env.example .env
```
Отредактируйте .env, указав:

- STEAM_API_KEY – ваш ключ Steam Web API.

- Настройки базы данных (если используете MySQL). Для тестирования без БД можно оставить DB_CONNECTION=sqlite и создать пустой файл database/database.sqlite.

3. **Запустите контейнеры**
```
docker-compose up -d
```
После запуска микросервис будет доступен по адресу http://localhost:8080.

4. **Выполните миграции (если используется БД)**
```
docker exec -it steam-stats-app php artisan migrate
```
5. **Проверьте работу**
```
curl -i http://localhost:8080/stats/123456789
```

6. **Остановка**
```
docker-compose down
```

7. Дополнительные замечания

- **Без базы данных** – если вы не используете БД, в `.env` установите `DB_CONNECTION=sqlite` и создайте файл `database/database.sqlite` (пустой). В `docker-compose.yml` можно удалить сервис `db`.
- **Кэширование конфигурации** – в `Dockerfile` мы уже выполнили `php artisan config:cache` и `route:cache`. Это улучшает производительность в продакшн.
- **Логи** – логи Laravel пишутся в `storage/logs`. Вы можете смонтировать эту папку для персистентности, но в примере она уже смонтирована.
- **Безопасность** – в продакшн следует использовать HTTPS, настроить правильные права доступа и ограничить доступ к API ключам.


**Запуск сервера**
```
php artisan serve
```

**Тесты**
Установка Pest
```
composer require pestphp/pest --dev
./vendor/bin/pest --init
```

```
./vendor/bin/pest
```