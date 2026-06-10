<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueDetail extends Model
{
    use HasFactory;
    protected $connection = 'sqlite_secondary';

    protected $fillable = [
        'revenue_id',
        'category_id',
        'amount',
        'percentage',
    ];

    public function revenue()
    {
        return $this->belongsTo(Revenue::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
