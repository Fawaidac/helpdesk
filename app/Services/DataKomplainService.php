<?php

namespace App\Services;

use App\Models\DataKomplain;

class DataKomplainService
{
    public function getAll($search = null, $kategori = null)
    {
        $query = DataKomplain::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('nama_pelapor', 'like', "%$search%")
                    ->orWhere('ruangan', 'like', "%$search%")
                    ->orWhere('permasalahan', 'like', "%$search%")
                    ->orWhere('nomor_wa', 'like', "%$search%");
            });
        }

        if ($kategori === 'simrs') {
            $query->where(function ($q) {
                $keywords = ['simrs', 'rme', 'lemot', 'loading', 'konek', 'internet'];
                foreach ($keywords as $word) {
                    $q->orWhere('permasalahan', 'like', "%$word%");
                }
            });
        } 
        
        if ($kategori === 'komputer') {
            $query->where(function ($q) {
                $keywords = ['komputer', 'printer', 'tinta', 'mouse', 'booting', 'cpu'];
                foreach ($keywords as $word) {
                    $q->orWhere('permasalahan', 'like', "%$word%");
                }
            });
        }

        return $query->get();
    }
}