<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\StatsController;

Route::get('/stats/{telegramId}', [StatsController::class, 'show']);