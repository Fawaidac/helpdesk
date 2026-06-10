<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['code' => 'umum', 'name' => 'UMUM'],
            ['code' => 'asuransi', 'name' => 'ASURANSI LAIN LAIN'],
            ['code' => 'bpjs', 'name' => 'BPJS KESEHATAN'],
            ['code' => 'spm', 'name' => 'SPM LUAR KOTA & BIAKESMASKIN'],
            ['code' => 'lain', 'name' => 'LAIN LAIN'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name']]
            );
        }
    }
}
