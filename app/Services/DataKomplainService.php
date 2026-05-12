<?php

namespace App\Services;

use App\Models\DataKomplain;
use Carbon\Carbon;

class DataKomplainService
{

    public function getAll(
        $search = null,
        $kategori = null,
        $isDone = null,
        $recent = null,
        $nomorAct = null
    ) {
        $query = DataKomplain::with('pde')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year);

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
                $keywords = [
                    'simrs',
                    'rme',
                    'lemot',
                    'loading',
                    'konek',
                    'internet'
                ];

                foreach ($keywords as $word) {
                    $q->orWhere('permasalahan', 'like', "%$word%");
                }
            });
        }

        if ($kategori === 'maintanance') {
            $query->where(function ($q) {
                $keywords = [
                    'komputer',
                    'printer',
                    'tinta',
                    'mouse',
                    'booting',
                    'cpu'
                ];

                foreach ($keywords as $word) {
                    $q->orWhere('permasalahan', 'like', "%$word%");
                }
            });
        }

        if ($isDone !== null) {
            if ($isDone) {
                $query->whereNotNull('nomor_act'); 
            } else {
                $query->whereNull('nomor_act'); 

                if ($recent) {
                    $query->where('tanggal', '>=', Carbon::now()->subHour());
                }
            }
        }

        if ($nomorAct !== null) {
            if ($nomorAct === 'null') {
                // ambil yang belum ditangani
                $query->whereNull('nomor_act');
            } else {
                $query->where('nomor_act', $nomorAct);
            }
        }

        return $query
            ->latest('id')
            ->paginate(10);
    }

    public function getDashboardCount()
    {
        $simrsKeywords = [
            'simrs',
            'rme',
            'lemot',
            'loading',
            'konek',
            'internet'
        ];

        $maintenanceKeywords = [
            'komputer',
            'printer',
            'tinta',
            'mouse',
            'booting',
            'cpu'
        ];

        $baseQuery = DataKomplain::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year);

        $doneQuery = function ($query) {
            $query->whereNotNull('nomor_act');
        };

        $openQuery = function ($query) {
            $query->where(function ($q) {
                $q->whereNull('nomor_act');
            });
        };

        $ticketOpen = (clone $baseQuery)->where($openQuery)->count();

        $ticketDone = (clone $baseQuery)->where($doneQuery)->count();

        $simrsMasuk = (clone $baseQuery)->where(function ($q) use ($simrsKeywords) {
            foreach ($simrsKeywords as $word) {
                $q->orWhere('permasalahan', 'like', "%$word%");
            }
        })->count();

        $simrsDone = (clone $baseQuery)
            ->where($doneQuery)
            ->where(function ($q) use ($simrsKeywords) {
                foreach ($simrsKeywords as $word) {
                    $q->orWhere('permasalahan', 'like', "%$word%");
                }
            })->count();

        $maintenanceMasuk = (clone $baseQuery)->where(function ($q) use ($maintenanceKeywords) {
            foreach ($maintenanceKeywords as $word) {
                $q->orWhere('permasalahan', 'like', "%$word%");
            }
        })->count();

        $maintenanceDone = (clone $baseQuery)
            ->where($doneQuery)
            ->where(function ($q) use ($maintenanceKeywords) {
                foreach ($maintenanceKeywords as $word) {
                    $q->orWhere('permasalahan', 'like', "%$word%");
                }
            })->count();

        $pdeTeamCounter = 1;

        $pdePerformance = (clone $baseQuery)
            ->selectRaw('nomor_act, COUNT(*) as total')
            ->whereNotNull('nomor_act')
            ->groupBy('nomor_act')
            ->with('pde')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) use (&$pdeTeamCounter) {

                $isPdeExist = $item->pde !== null;

                return [
                    'id' => $item->pde->id ?? null,
                    'nama' => $isPdeExist
                        ? $item->pde->nama
                        : 'PDE Team ' . $pdeTeamCounter++,
                    'alamat' => $item->pde->alamat ?? null,
                    'telp' => $item->nomor_act,
                    'total' => $item->total,
                ];
            });
            
        return [
            'ticket_open' => $ticketOpen,
            'ticket_done' => $ticketDone,

            'simrs_masuk' => $simrsMasuk,
            'simrs_done' => $simrsDone,

            'maintenance_masuk' => $maintenanceMasuk,
            'maintenance_done' => $maintenanceDone,
            'pde_performance' => $pdePerformance,
        ];
    }

    public function getDataTeamPde()
    {
        $pdeTeamCounter = 1;

        $data = DataKomplain::with('pde')
            ->whereNotNull('nomor_act')
            ->select('nomor_act')
            ->distinct()
            ->orderBy('nomor_act')
            ->get()
            ->map(function ($item) use (&$pdeTeamCounter) {

                $isPdeExist = $item->pde !== null;

                return [
                    'id' => $item->pde->id ?? null,
                    'nama' => $isPdeExist
                        ? $item->pde->nama
                        : 'PDE Team ' . $pdeTeamCounter++,
                    'alamat' => $item->pde->alamat ?? null,
                    'telp' => $item->nomor_act,
                ];
            });

        return $data->values();
    }
}