<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataKomplain extends Model
{
    protected $table = 'data_komplain';

    protected $guarded = [];

    protected $appends = ['status'];

    public function getStatusAttribute()
    {
        $isDone =
            $this->nomor_act !== null;

        return $isDone ? 'DONE' : 'PENDING';
    }

    public function toArray()
    {
        $array = parent::toArray();

        if (
            empty($array['pde']) &&
            !empty($this->nomor_act)
        ) {
            $array['pde'] = [
                'id' => null,
                'nama' => 'PDE Team',
                'alamat' => null,
                'telp' => $this->nomor_act,
            ];
        }

        return $array;
    }

    public function pde()
    {
        return $this->belongsTo(
            DataPde::class,
            'nomor_act', 
            'telp' 
        );
    }
}