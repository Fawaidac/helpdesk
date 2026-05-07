<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataKomplain extends Model
{
    protected $table = 'data_komplain';

    protected $guarded = [];

    public function pde()
    {
        return $this->belongsTo(
            DataPde::class,
            'nomor_act', 
            'telp' 
        );
    }
}