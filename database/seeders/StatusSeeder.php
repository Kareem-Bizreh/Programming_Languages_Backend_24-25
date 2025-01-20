<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statuses')->insert([
            'name_en' => 'pinding',
            'name_ar' => 'قيد الانتظار'
        ]);
        DB::table('statuses')->insert([
            'name_en' => 'Delivering',
            'name_ar' => 'قيد التوصيل'
        ]);
        DB::table('statuses')->insert([
            'name_en' => 'completed',
            'name_ar' => 'تم الطلب'
        ]);
        DB::table('statuses')->insert([
            'name_en' => 'rejected',
            'name_ar' => 'تم رفض الطلب'
        ]);
        DB::table('statuses')->insert([
            'name_en' => 'cancelled',
            'name_ar' => 'تم الغاء الطلب'
        ]);
    }
}