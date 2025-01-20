<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name_ar' => 'أطعمة ومشروبات', 'name_en' => 'Food and Drinks'],
            ['name_ar' => 'إلكترونيات', 'name_en' => 'Electronics'],
            ['name_ar' => 'ملابس وأزياء', 'name_en' => 'Clothing and Fashion'],
            ['name_ar' => 'أدوات منزلية', 'name_en' => 'Home Appliances'],
            ['name_ar' => 'مستلزمات مكتبية', 'name_en' => 'Office Supplies'],
            ['name_ar' => 'مستلزمات أطفال', 'name_en' => 'Baby Supplies'],
            ['name_ar' => 'أدوية وصيدليات', 'name_en' => 'Medicine and Pharmacies'],
            ['name_ar' => 'بقالة ومستلزمات يومية', 'name_en' => 'Groceries and Daily Needs'],
            ['name_ar' => 'كتب ومجلات', 'name_en' => 'Books and Magazines'],
            ['name_ar' => 'ألعاب وهدايا', 'name_en' => 'Toys and Gifts'],
            ['name_ar' => 'خدمات وصيانة', 'name_en' => 'Services and Maintenance'],
            ['name_ar' => 'أثاث وديكور', 'name_en' => 'Furniture and Decoration'],
            ['name_ar' => 'منتجات تجميل وعناية شخصية', 'name_en' => 'Beauty and Personal Care'],
            ['name_ar' => 'رياضة ولياقة', 'name_en' => 'Sports and Fitness'],
            ['name_ar' => 'مستلزمات الحيوانات الأليفة', 'name_en' => 'Pet Supplies'],
        ];

        DB::table('categories')->insert($categories);
    }
}
