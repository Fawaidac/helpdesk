<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    use HasFactory;
    protected $connection = 'sqlite_secondary';

    protected $fillable = [
        'tahun',
        'bulan',
    ];

    public function details()
    {
        return $this->hasMany(RevenueDetail::class);
    }

    public function getTotalAttribute()
    {
        return $this->details->sum('amount');
    }

    protected $appends = ['bulan_name'];

    const BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function getBulanNameAttribute()
    {
        return self::BULAN[$this->bulan] ?? null;
    }
}
