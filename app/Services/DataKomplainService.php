<?php

namespace App\Services;

use App\Models\DataKomplain;

class DataKomplainService
{

    public function getAll(
    $search = null,
    $kategori = null,
    $isDone = null,
    ) {
        $query = DataKomplain::with('pde');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('nama_pelapor', 'like', "%$search%")
                    ->orWhere('ruangan', 'like', "%$search%")
                    ->orWhere('permasalahan', 'like', "%$search%")
                    ->orWhere('nomor_wa', 'like', "%$search%");
            });
        }

        /// FILTER KATEGORI
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
                    $q->orWhere(
                        'permasalahan',
                        'like',
                        "%$word%"
                    );
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
                    $q->orWhere(
                        'permasalahan',
                        'like',
                        "%$word%"
                    );
                }
            });
        }

        if ($isDone !== null) {

            if ($isDone) {
                $query->whereNotNull('nomor_act');
            }

            else {
                $query->where(function ($q) {
                    $q->whereNull('nomor_act');
                });
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

        /// QUERY STATUS DONE
        $doneQuery = function ($query) {
            $query->whereNotNull('nomor_act')
                ->whereNotNull('tanggal_act')
                ->where(
                    'tanggal_act',
                    '!=',
                    '0000-00-00 00:00:00'
                );
        };

        /// QUERY STATUS OPEN
        $openQuery = function ($query) {
            $query->where(function ($q) {
                $q->whereNull('nomor_act')
                    ->orWhereNull('tanggal_act')
                    ->orWhere(
                        'tanggal_act',
                        '0000-00-00 00:00:00'
                    );
            });
        };

        /// TOTAL
        $ticketOpen = DataKomplain::where($openQuery)->count();

        $ticketDone = DataKomplain::where($doneQuery)->count();

        /// SIMRS
        $simrsMasuk = DataKomplain::where(function ($q) use ($simrsKeywords) {
            foreach ($simrsKeywords as $word) {
                $q->orWhere(
                    'permasalahan',
                    'like',
                    "%$word%"
                );
            }
        })->count();

        $simrsDone = DataKomplain::where($doneQuery)
            ->where(function ($q) use ($simrsKeywords) {
                foreach ($simrsKeywords as $word) {
                    $q->orWhere(
                        'permasalahan',
                        'like',
                        "%$word%"
                    );
                }
            })->count();

        /// MAINTENANCE
        $maintenanceMasuk = DataKomplain::where(function ($q) use ($maintenanceKeywords) {
            foreach ($maintenanceKeywords as $word) {
                $q->orWhere(
                    'permasalahan',
                    'like',
                    "%$word%"
                );
            }
        })->count();

        $maintenanceDone = DataKomplain::where($doneQuery)
            ->where(function ($q) use ($maintenanceKeywords) {
                foreach ($maintenanceKeywords as $word) {
                    $q->orWhere(
                        'permasalahan',
                        'like',
                        "%$word%"
                    );
                }
            })->count();

        return [
            'ticket_open' => $ticketOpen,
            'ticket_done' => $ticketDone,

            'simrs_masuk' => $simrsMasuk,
            'simrs_done' => $simrsDone,

            'maintenance_masuk' => $maintenanceMasuk,
            'maintenance_done' => $maintenanceDone,
        ];
    }
}