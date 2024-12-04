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
            'name_en' => 'Order Being Prepared',
            'name_ar' => 'يتم تجهيز الطلب'
        ]);
        DB::table('statuses')->insert([
            'name_en' => 'Delivering',
            'name_ar' => 'جار التوصيل'
        ]);
        DB::table('statuses')->insert([
            'name_en' => 'Delivered',
            'name_ar' => 'تم التوصيل'
        ]);
        DB::table('statuses')->insert([
            'name_en' => 'Order has been accepted',
            'name_ar' => 'تم قبول الطلب'
        ]);
        DB::table('statuses')->insert([
            'name_en' => 'Order has been rejected',
            'name_ar' => 'تم رفض الطلب'
        ]);
    }
}