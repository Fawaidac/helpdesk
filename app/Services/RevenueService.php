<?php

namespace App\Services;

use App\Models\Revenue;
use App\Models\Category;
use App\Models\RevenueDetail;
use App\Models\Target;

class RevenueService
{
    public function getDashboard(int $tahun)
    {
        if (!$tahun) {
            throw new \Exception('Tahun wajib diisi');
        }

        $categories = Category::all();
        $targets = Target::where('tahun', $tahun)->get();

        $targetTahunan = [];
        $targetBulanan = [];

        foreach ($categories as $cat) {
            $targetTahunanRow = $targets->where('category_id', $cat->id)->where('type', 'tahunan')->first();
            $targetBulananRow = $targets->where('category_id', $cat->id)->where('type', 'bulanan')->first();

            $targetTahunan[$cat->name] = $targetTahunanRow ? $targetTahunanRow->amount : 0;
            $targetBulanan[$cat->name] = $targetBulananRow ? $targetBulananRow->amount : 0;
        }

        $jumlahTargetTahunan = array_sum($targetTahunan);
        $jumlahTargetBulanan = array_sum($targetBulanan);

        $revenues = Revenue::with('details.category')
            ->where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();

        $realisasiData = [];
        $summaryKategori = [];

        foreach ($categories as $cat) {
            $summaryKategori[$cat->name] = 0;
        }

        foreach (Revenue::BULAN as $bulan => $bulanName) {

            $rev = $revenues->firstWhere('bulan', $bulan);

            $dataKategori = [];
            $totalBulan = 0;

            foreach ($categories as $cat) {

                $detail = $rev?->details?->firstWhere('category_id', $cat->id);
                $amount = $detail ? $detail->amount : 0;
                $targetBulan = $targetBulanan[$cat->name] ?? 0;

                $percentage = $targetBulan > 0
                    ? number_format(($amount / $targetBulan) * 100, 2, '.', '')
                    : '0.00';

                $dataKategori[] = [
                    'category' => $cat->name,
                    'amount' => $amount,
                    'percentage' => $percentage
                ];

                $totalBulan += $amount;
                $summaryKategori[$cat->name] += $amount;
            }

            $totalPercentage = $jumlahTargetBulanan > 0
                ? number_format(($totalBulan / $jumlahTargetBulanan) * 100, 2, '.', '')
                : '0.00';

            $realisasiData[] = [
                'bulan' => $bulan,
                'bulan_name' => $bulanName,
                'categories' => $dataKategori,
                'total_bulan' => $totalBulan, 
                'total_percentage' => $totalPercentage 
            ];
        }

        $persentaseTahun = [];
        foreach ($categories as $cat) {
            $target = $targetTahunan[$cat->name] ?? 0;
            $realisasiCat = $summaryKategori[$cat->name];

            $persentaseTahun[$cat->name] = $target > 0
                ? number_format(($realisasiCat / $target) * 100, 2, '.', '')
                : '0.00';
        }

        $grandTotalRealisasi = array_sum($summaryKategori);
        
        $grandPersentaseTahun = $jumlahTargetTahunan > 0
            ? number_format(($grandTotalRealisasi / $jumlahTargetTahunan) * 100, 2, '.', '')
            : '0.00';

        return [
            'tahun' => $tahun,

            'target_tahunan' => $targetTahunan,
            'total_target_tahunan' => $jumlahTargetTahunan, 
            
            'target_bulanan' => $targetBulanan,
            'total_target_bulanan' => $jumlahTargetBulanan, 

            'realisasi' => $realisasiData,

            'summary' => [
                'total_per_kategori' => $summaryKategori,     
                'persentase_tahun' => $persentaseTahun,       
                'grand_total_realisasi' => $grandTotalRealisasi, 
                'grand_persentase_tahun' => $grandPersentaseTahun
            ]
        ];
    }

    public function storeOrUpdate(array $data)
    {
        $tahun = $data['tahun'];
        
        $categories = Category::all();

        foreach ($data['targets'] as $targetInput) {
            $category = $categories->firstWhere('code', $targetInput['category_code']);
            
            if (!$category) {
                throw new \Exception("Kategori dengan code '{$targetInput['category_code']}' tidak ditemukan.");
            }

            Target::updateOrCreate(
                [
                    'tahun' => $tahun,
                    'category_id' => $category->id,
                    'type' => 'tahunan',
                ],
                [
                    'amount' => $targetInput['target_tahunan'] ?? 0,
                ]
            );

            Target::updateOrCreate(
                [
                    'tahun' => $tahun,
                    'category_id' => $category->id,
                    'type' => 'bulanan',
                ],
                [
                    'amount' => $targetInput['target_bulanan'] ?? 0,
                ]
            );
        }

        if (isset($data['realisasi']) && is_array($data['realisasi'])) {
            foreach ($data['realisasi'] as $realisasiBulan) {
                $bulan = $realisasiBulan['bulan'];

                $revenue = Revenue::updateOrCreate(
                    [
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                    ],
                    []
                );

                foreach ($realisasiBulan['categories'] as $catInput) {
                    
                    $category = $categories->firstWhere('code', $catInput['category_code']);

                    if (!$category) {
                        throw new \Exception("Kategori dengan code '{$catInput['category_code']}' tidak ditemukan.");
                    }

                    $targetBulananRow = Target::where('tahun', $tahun)
                        ->where('category_id', $category->id)
                        ->where('type', 'bulanan')
                        ->first();

                    $targetBulananAmount = $targetBulananRow ? $targetBulananRow->amount : 0;
                    $amountRealisasi = $catInput['amount'] ?? 0;

                    $percentage = $targetBulananAmount > 0
                        ? number_format(($amountRealisasi / $targetBulananAmount) * 100, 2, '.', '')
                        : '0.00';

                    RevenueDetail::updateOrCreate(
                        [
                            'revenue_id' => $revenue->id,
                            'category_id' => $category->id,
                        ],
                        [
                            'amount' => $amountRealisasi,
                            'percentage' => $percentage,
                        ]
                    );
                }
            }
        }

        return true;
    }

    public function getYearList(): array
    {
        $targetYears = Target::pluck('tahun')->toArray();
        $revenueYears = Revenue::pluck('tahun')->toArray();

        $allYears = array_unique(array_merge($targetYears, $revenueYears));
        sort($allYears); 

        return array_values($allYears);
    }

    
    public function getByYear(int $tahun): array
    {
        $categories = Category::all();
        
        $targets = Target::where('tahun', $tahun)->get();

        $targetData = [];
        foreach ($categories as $cat) {
            $tahunan = $targets->where('category_id', $cat->id)->where('type', 'tahunan')->first();
            $bulanan = $targets->where('category_id', $cat->id)->where('type', 'bulanan')->first();

            $targetData[] = [
                'category_code' => $cat->code,
                'category_name' => $cat->name,
                'target_tahunan' => $tahunan ? $tahunan->amount : 0,
                'target_bulanan' => $bulanan ? $bulanan->amount : 0,
            ];
        }

        $revenues = Revenue::with('details')->where('tahun', $tahun)->orderBy('bulan')->get();

        $realisasiData = [];
        foreach ($revenues as $revenue) {
            $categoriesData = [];
            
            foreach ($categories as $cat) {
                $detail = $revenue->details->firstWhere('category_id', $cat->id);
                
                $categoriesData[] = [
                    'category_code' => $cat->code,
                    'category_name' => $cat->name,
                    'amount' => $detail ? $detail->amount : 0,
                ];
            }

            $realisasiData[] = [
                'bulan' => $revenue->bulan,
                'bulan_name' => Revenue::BULAN[$revenue->bulan] ?? '',
                'categories' => $categoriesData
            ];
        }

        return [
            'tahun' => $tahun,
            'targets' => $targetData,
            'realisasi' => $realisasiData
        ];
    }
}