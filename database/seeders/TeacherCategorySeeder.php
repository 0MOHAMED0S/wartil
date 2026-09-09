<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'الفئة الأولى', 'egypt_rate' => 80, 'arab_rate' => 120, 'foreign_rate' => 160],
            ['name' => 'الفئة الثانية', 'egypt_rate' => 100, 'arab_rate' => 150, 'foreign_rate' => 200],
            ['name' => 'الفئة الثالثة', 'egypt_rate' => 130, 'arab_rate' => 195, 'foreign_rate' => 260],
            ['name' => 'الفئة الرابعة', 'egypt_rate' => 170, 'arab_rate' => 255, 'foreign_rate' => 340],
            ['name' => 'الفئة الخامسة', 'egypt_rate' => 220, 'arab_rate' => 330, 'foreign_rate' => 440],
        ];

        foreach ($categories as $cat) {
            \App\Models\TeacherCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // Set regions for existing countries based on code
        \Illuminate\Support\Facades\DB::table('countries')->whereIn('code', ['EG', 'eg'])->update(['region' => 'egypt']);
        \Illuminate\Support\Facades\DB::table('countries')->whereIn('code', [
            'SA', 'sa', 'AE', 'ae', 'KW', 'kw', 'QA', 'qa', 'BH', 'bh', 'OM', 'om', 
            'JO', 'jo', 'LB', 'lb', 'SY', 'sy', 'IQ', 'iq', 'YE', 'ye', 'SD', 'sd', 
            'LY', 'ly', 'TN', 'tn', 'DZ', 'dz', 'MA', 'ma', 'MR', 'mr', 'SO', 'so', 
            'DJ', 'dj', 'KM', 'km', 'PS', 'ps'
        ])->update(['region' => 'arab']);
        
        \Illuminate\Support\Facades\DB::table('countries')
            ->whereNotIn('region', ['egypt', 'arab'])
            ->update(['region' => 'foreign']);
    }
}
