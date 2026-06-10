<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Revenue;
use App\Models\RevenueDetail;
use App\Models\Category;
use App\Models\Target;

class RevenueSeeder extends Seeder
{
    public function run(): void
    {   
        RevenueDetail::truncate();
        Revenue::truncate();
        Target::truncate(); 

        $categories = [
            ['name' => 'UMUM', 'code' => 'umum'],
            ['name' => 'ASURANSI LAIN LAIN', 'code' => 'asuransi'],
            ['name' => 'BPJS KESEHATAN', 'code' => 'bpjs'],
            ['name' => 'SPM LUAR KOTA & BIAKESMASKIN', 'code' => 'spm'],
            ['name' => 'LAIN LAIN', 'code' => 'lain'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['code' => $cat['code']],
                $cat
            );
        }

        $categories = Category::all();

        $targetDataTahunan = [
            'umum' => 2803200000,
            'asuransi' => 15261000000,
            'bpjs' => 284856000000,
            'spm' => 1254000000,
            'lain' => 6930000000,
        ];

        $targetDataBulanan = [
            'umum' => 233600000,
            'asuransi' => 1271750000,
            'bpjs' => 23738000000,
            'spm' => 104500000,
            'lain' => 577500000,
        ];

        foreach ($categories as $cat) {
            Target::updateOrCreate(
                [
                    'tahun' => 1900,
                    'category_id' => $cat->id,
                    'type' => 'bulanan',
                ],
                [
                    'amount' => $targetDataBulanan[$cat->code] ?? 0,
                ]
            );
            Target::updateOrCreate(
                [
                    'tahun' => 1900,
                    'category_id' => $cat->id,
                    'type' => 'tahunan',
                ],
                [
                    'amount' => $targetDataTahunan[$cat->code] ?? 0,
                ]
            );

            
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {

            $revenue = Revenue::updateOrCreate(
                [
                    'tahun' => 1900,
                    'bulan' => $bulan,
                ],
                []
            );

            foreach ($categories as $cat) {
                switch ($cat->code) {
                    case 'umum': $amount = 2000000000; break;
                    case 'asuransi': $amount = 1500000000; break;
                    case 'bpjs': $amount = 10000000000; break;
                    case 'spm': $amount = 500000000; break;
                    case 'lain': $amount = 800000000; break;
                    default: $amount = 1000000000;
                }

                $targetBulanan = $targetDataBulanan[$cat->code] ?? 0;

                $percentage = $targetBulanan > 0
                    ? number_format(($amount / $targetBulanan) * 100, 2, '.', '')
                    : '0.00';
                
                RevenueDetail::updateOrCreate(
                    [
                        'revenue_id' => $revenue->id,
                        'category_id' => $cat->id,
                    ],
                    [
                        'amount' => $amount,
                        'percentage' => $percentage,
                    ]
                );
            }
        }
    }
}