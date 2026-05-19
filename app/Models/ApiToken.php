<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $connection = 'sqlite_secondary';

    protected $fillable = [
        'user_id',
        'token',
        'expired_at'
    ];
}