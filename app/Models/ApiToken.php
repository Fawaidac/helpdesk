<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $connection = 'mysql_secondary';

    protected $fillable = [
        'user_id',
        'token',
        'expired_at'
    ];
}