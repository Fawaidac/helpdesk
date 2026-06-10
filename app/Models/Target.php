<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $connection = 'sqlite_secondary';

    protected $fillable = [
        'tahun',
        'category_id',
        'amount',
        'type',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
