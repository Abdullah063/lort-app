<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use App\Models\Translation;
use App\Models\Goal;
use App\Models\Interest;

class CeviriSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // HEDEFLER - İNGİLİZCE
        // =============================================
        $goalTranslations = [
            'Ürün Satmak'                       => 'Sell Products',
            'Hizmet Satmak'                     => 'Sell Services',
            'Network Genişletmek'               => 'Expand Network',
            'E-Ticaret Kurmak'                  => 'Start E-Commerce',
            'Bayilik Kurmak'                    => 'Build Dealership',
            'Franchise Almak'                   => 'Get Franchise',
            'Marka Bilinirliği'                 => 'Brand Awareness',
            'Lojistik İmkanları'                => 'Logistics Opportunities',
            'Mamul Yarı Mamul Hammadde Temini'  => 'Raw Material Supply',
        ];

        // HEDEFLER - İNGİLİZCE - AÇIKLAMALAR
        $goalDescTranslations = [
            'Ürün Satmak'                       => 'Introduce your products to international markets',
            'Hizmet Satmak'                     => 'Offer your services worldwide',
            'Network Genişletmek'               => 'Build commercial relationships and expand your network',
            'E-Ticaret Kurmak'                  => 'Build your online sales infrastructure',
            'Bayilik Kurmak'                    => 'Expand your dealership network',
            'Franchise Almak'                   => 'Acquire franchises from successful brands',
            'Marka Bilinirliği'                 => 'Introduce your brand to wider audiences',
            'Lojistik İmkanları'                => 'Discover and evaluate logistics opportunities',
            'Mamul Yarı Mamul Hammadde Temini'  => 'Supply raw materials, semi-finished and finished products',
        ];

        $goals = Goal::all();
        foreach ($goals as $goal) {
            if (isset($goalTranslations[$goal->name])) {
                Translation::create([
                    'table_name'    => 'goals',
                    'record_id'     => $goal->id,
                    'field_name'    => 'name',
                    'language_code' => 'en',
                    'value'         => $goalTranslations[$goal->name],
                ]);
            }
            if (isset($goalDescTranslations[$goal->name])) {
                Translation::create([
                    'table_name'    => 'goals',
                    'record_id'     => $goal->id,
                    'field_name'    => 'description',
                    'language_code' => 'en',
                    'value'         => $goalDescTranslations[$goal->name],
                ]);
            }
        }

        // =============================================
        // HEDEFLER - ARAPÇA
        // =============================================
        $goalTranslationsAr = [
            'Ürün Satmak'                       => 'بيع المنتجات',
            'Hizmet Satmak'                     => 'بيع الخدمات',
            'Network Genişletmek'               => 'توسيع الشبكة',
            'E-Ticaret Kurmak'                  => 'إنشاء التجارة الإلكترونية',
            'Bayilik Kurmak'                    => 'بناء شبكة الوكلاء',
            'Franchise Almak'                   => 'الحصول على امتياز',
            'Marka Bilinirliği'                 => 'الوعي بالعلامة التجارية',
            'Lojistik İmkanları'                => 'فرص اللوجستية',
            'Mamul Yarı Mamul Hammadde Temini'  => 'توريد المواد الخام',
        ];

        // HEDEFLER - ARAPÇA - AÇIKLAMALAR
        $goalDescTranslationsAr = [
            'Ürün Satmak'                       => 'قدم منتجاتك إلى الأسواق الدولية',
            'Hizmet Satmak'                     => 'قدم خدماتك في جميع أنحاء العالم',
            'Network Genişletmek'               => 'بناء علاقات تجارية وتوسيع شبكتك',
            'E-Ticaret Kurmak'                  => 'بناء بنية تحتية للمبيعات عبر الإنترنت',
            'Bayilik Kurmak'                    => 'توسيع شبكة الوكلاء الخاصة بك',
            'Franchise Almak'                   => 'احصل على امتياز من العلامات التجارية الناجحة',
            'Marka Bilinirliği'                 => 'عرف علامتك التجارية لجمهور أوسع',
            'Lojistik İmkanları'                => 'اكتشف وقيّم الفرص اللوجستية',
            'Mamul Yarı Mamul Hammadde Temini'  => 'توريد المواد الخام وشبه المصنعة والمصنعة',
        ];

        foreach ($goals as $goal) {
            if (isset($goalTranslationsAr[$goal->name])) {
                Translation::create([
                    'table_name'    => 'goals',
                    'record_id'     => $goal->id,
                    'field_name'    => 'name',
                    'language_code' => 'ar',
                    'value'         => $goalTranslationsAr[$goal->name],
                ]);
            }
            if (isset($goalDescTranslationsAr[$goal->name])) {
                Translation::create([
                    'table_name'    => 'goals',
                    'record_id'     => $goal->id,
                    'field_name'    => 'description',
                    'language_code' => 'ar',
                    'value'         => $goalDescTranslationsAr[$goal->name],
                ]);
            }
        }

        // =============================================
        // İLGİ ALANLARI - İNGİLİZCE
        // =============================================
        $interestTranslations = [
            'Ekonomi Grupları'              => 'Economic Groups',
            'Ekonomik Topluluklar'          => 'Economic Communities',
            'Eğitim Dernekleri'             => 'Educational Associations',
            'Gastronomi'                    => 'Gastronomy',
            'Müzeler'                       => 'Museums',
            'Müzik'                         => 'Music',
            'Seyahat / Gezi'                => 'Travel',
            'Sosyal Kurumlar'               => 'Social Institutions',
            'Spor'                          => 'Sports',
            'Ticaret Odaları'               => 'Chambers of Commerce',
            'Yardım Dernekleri'             => 'Charity Associations',
            'İnsan ve Kültürel Servisler'   => 'Human and Cultural Services',
        ];

        $interests = Interest::all();
        foreach ($interests as $interest) {
            if (isset($interestTranslations[$interest->name])) {
                Translation::create([
                    'table_name'    => 'interests',
                    'record_id'     => $interest->id,
                    'field_name'    => 'name',
                    'language_code' => 'en',
                    'value'         => $interestTranslations[$interest->name],
                ]);
            }
        }

        // =============================================
        // İLGİ ALANLARI - ARAPÇA
        // =============================================
        $interestTranslationsAr = [
            'Ekonomi Grupları'              => 'المجموعات الاقتصادية',
            'Ekonomik Topluluklar'          => 'المجتمعات الاقتصادية',
            'Eğitim Dernekleri'             => 'جمعيات التعليم',
            'Gastronomi'                    => 'فن الطهي',
            'Müzeler'                       => 'المتاحف',
            'Müzik'                         => 'الموسيقى',
            'Seyahat / Gezi'                => 'السفر والرحلات',
            'Sosyal Kurumlar'               => 'المؤسسات الاجتماعية',
            'Spor'                          => 'الرياضة',
            'Ticaret Odaları'               => 'غرف التجارة',
            'Yardım Dernekleri'             => 'جمعيات الخيرية',
            'İnsan ve Kültürel Servisler'   => 'الخدمات الإنسانية والثقافية',
        ];

        foreach ($interests as $interest) {
            if (isset($interestTranslationsAr[$interest->name])) {
                Translation::create([
                    'table_name'    => 'interests',
                    'record_id'     => $interest->id,
                    'field_name'    => 'name',
                    'language_code' => 'ar',
                    'value'         => $interestTranslationsAr[$interest->name],
                ]);
            }
        }

        // =============================================
        // KATEGORİLER
        // =============================================
        $categories = Category::all();

        $catTranslations = [
            'individual' => ['tr' => 'Bireysel',  'en' => 'Individual', 'ar' => 'فردي'],
            'corporate'  => ['tr' => 'Kurumsal',  'en' => 'Corporate',  'ar' => 'شركات'],
            'other'      => ['tr' => 'Diğer',     'en' => 'Other',      'ar' => 'أخرى'],
        ];

        foreach ($categories as $cat) {
            if (isset($catTranslations[$cat->code])) {
                foreach ($catTranslations[$cat->code] as $langCode => $value) {
                    Translation::updateOrCreate([
                        'table_name'    => 'categories',
                        'record_id'     => $cat->id,
                        'field_name'    => 'name',
                        'language_code' => $langCode,
                    ], [
                        'value' => $value,
                    ]);
                }
            }
        }

        $this->command->info('Çeviriler eklendi (EN + TR + AR)');

    }
}
