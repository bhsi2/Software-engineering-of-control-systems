Был создан набор тестов, которые покрывают основные сценарии работы микросервиса. Они делятся на три группы:

- **Unit-тесты для `SteamApiService`** – проверяют, что сервис корректно формирует запросы к Steam API и обрабатывает ответы/ошибки.
- **Unit-тесты для `SteamStatsProvider`** – проверяют бизнес-логику агрегации статистики, не обращаясь к реальному API.


## 1. Тесты `SteamApiServiceTest` (Unit)

Эти тесты проверяют, что `SteamApiService` правильно:
- строит URL с параметрами,
- отправляет запрос через Guzzle,
- возвращает распарсенные данные при успешном ответе,
- возвращает `null` или 0 при ошибках сети или API.

### Используемые моки
- `MockHandler` из Guzzle – имитирует HTTP-ответы.
- `Middleware::history()` – сохраняет историю запросов для проверки.
- `ReflectionClass` – подменяет клиент в сервисе на моковый.

### Тесты

**`it fetches player summary successfully`**
- Проверяет, что при успешном ответе от Steam API метод `getPlayerSummary` возвращает массив с данными игрока.
- Мокает ответ с корректным JSON.
- Проверяет, что был сделан ровно один запрос и URL содержит правильные параметры.

**`it returns null on player summary failure`**
- Мокает ответ с кодом 500.
- Ожидает, что метод вернёт `null` (исключение перехватывается, возвращается `null`).

**`it fetches owned games successfully`**
- Аналогично проверяет успешное получение списка игр: мокает ответ, проверяет возвращаемые данные и URL.

**`it returns null on owned games failure`**
- Проверяет обработку ошибки при запросе игр.

**`it fetches steam level successfully`**
- Проверяет получение уровня Steam.

**`it returns null on steam level failure`**
- Проверяет обработку ошибки при запросе уровня.

**`it fetches friend count successfully`**
- Проверяет получение количества друзей (список друзей, возвращаемый API).
- Убеждается, что возвращается корректное число (2).

**`it returns 0 when friend list is empty`**
- Мокает ответ с пустым списком друзей.
- Ожидает `0`.

**`it returns 0 on friend list failure`**
- Мокает ошибку 500.
- Ожидает `0`, так как в случае ошибки метод возвращает 0.

---

## 2. Тесты `SteamStatsProviderTest` (Unit)

Эти тесты проверяют, что провайдер правильно агрегирует данные из разных методов `SteamApiService` и формирует DTO.

### Используемые моки
- `Mockery::mock(SteamApiService::class)` – подменяем реальный API-сервис.
- Настраиваем ожидания вызовов методов и возвращаемые значения.

### Тесты

**`it returns stats for public profile`**
- Настраиваем мок так, чтобы:
  - `getPlayerSummary` вернул полные данные публичного профиля.
  - `getOwnedGames` вернул массив из двух игр.
  - `getSteamLevel` вернул 50.
  - `getFriendCount` вернул 100.
- Запускаем `getStats` и проверяем, что все поля DTO заполнены ожидаемыми значениями, в том числе преобразованные (часы из минут, статус `online`, видимость `public`).

**`it handles no games (empty library)`**
- Мокаем, что список игр пуст.
- Проверяем, что `gamesCount = 0`, `hoursTotal = 0`, `topGame = '—'`.

**`it handles private profile`**
- Мокаем:
  - `getPlayerSummary` возвращает только ник и статус приватности.
  - Все остальные методы возвращают `null` (игр нет, уровень не получен, друзей нет).
- Проверяем, что `gamesCount`, `hoursTotal`, `steamLevel`, `friendCount` равны 0 или `—`, а `communityVisibility` = `"private"`.

**`it throws exception when player summary missing`**
- Мокаем `getPlayerSummary` возвращает `null`.
- Ожидаем, что метод `getStats` выбросит `RuntimeException` с сообщением "Steam profile not found".

