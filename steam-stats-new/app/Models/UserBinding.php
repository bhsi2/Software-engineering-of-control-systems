<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBinding extends Model
{
    protected $table = 'user_bindings';
    protected $fillable = ['telegram_id', 'steam_id'];
}