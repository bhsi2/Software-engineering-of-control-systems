<?php
namespace App\Repositories;

use App\Models\UserBinding;

class UserBindingRepository
{
    public function findSteamIdByTelegramId(int $telegramId): ?string
    {
        $binding = UserBinding::where('telegram_id', $telegramId)->first();
        return $binding?->steam_id;
    }
}