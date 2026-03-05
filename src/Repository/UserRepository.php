<?php
namespace App\Repository;

use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает SteamID64 по Telegram ID или null, если привязки нет.
     */
    public function findSteamIdByTelegramId(int $telegramId): ?string
    {
        $stmt = $this->db->prepare('SELECT steam_id FROM user_bindings WHERE telegram_id = :tid');
        $stmt->execute(['tid' => $telegramId]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (string) $result : null;
    }
}