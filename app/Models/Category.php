<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $connection = 'sqlite_secondary';


    protected $fillable = [
        'name',
        'code',
    ];

    // relasi ke revenue detail
    public function revenueDetails()
    {
        return $this->hasMany(RevenueDetail::class);
    }

    // relasi ke target
    public function targets()
    {
        return $this->hasMany(Target::class);
    }
}
