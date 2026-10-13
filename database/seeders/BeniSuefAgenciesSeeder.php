<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\AgentProfile;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BeniSuefAgenciesSeeder extends Seeder
{
    public function run(): void
    {
        $agentRole = Role::firstOrCreate(['slug' => 'agent'], ['name' => 'سمسار', 'level' => 30]);

        $companies = [
            [
                'agency_name' => 'شركة النيل للتطوير والاستثمار العقاري',
                'agency_name_en' => 'Nile Real Estate Development',
                'slug' => 'nile-real-estate-development',
                'city' => 'مدينة بني سويف',
                'city_en' => 'Beni Suef City',
                'location_mark' => 'كورنيش النيل، مدينة بني سويف',
                'location_mark_en' => 'Nile Corniche, Beni Suef City',
                'phone' => '01001234561',
                'whatsapp' => '201001234561',
                'email' => 'nile@darlii.eg',
                'job_title' => 'شركة تطوير واستثمار عقاري معتمدة',
                'job_title_en' => 'Certified Real Estate Developer',
                'agent_name' => 'م. أحمد عبد العزيز النيلي',
                'agent_name_en' => 'Eng. Ahmed Abdelaziz',
                'years_experience' => 14,
                'license_number' => 'BS-RE-2012-088',
                'avatar' => 'storage/avatars/1.jpg',
                'bio' => 'كبرى شركات التطوير والوساطة العقارية في محافظة بني سويف، متخصصة في المشروعات السكنية والتجارية الفاخرة على كورنيش النيل وبني سويف الجديدة.',
                'bio_en' => 'Leading real estate development and brokerage firm in Beni Suef, specialising in luxury residential and commercial projects along the Nile Corniche and New Beni Suef.',
            ],
            [
                'agency_name' => 'مجموعة الصعيد للتعمير والاستثمار',
                'agency_name_en' => 'Upper Egypt Development Group',
                'slug' => 'upper-egypt-development-group',
                'city' => 'مدينة بني سويف الجديدة',
                'city_en' => 'New Beni Suef City',
                'location_mark' => 'الحي الثاني، بني سويف الجديدة',
                'location_mark_en' => 'District 2, New Beni Suef City',
                'phone' => '01112345672',
                'whatsapp' => '201112345672',
                'email' => 'saeed@darlii.eg',
                'job_title' => 'مجموعة تطوير عمراني ومقاولات',
                'job_title_en' => 'Urban Development & Construction Group',
                'agent_name' => 'أ. محمود فاروق الصعيدي',
                'agent_name_en' => 'Mahmoud Farouk El Saeedi',
                'years_experience' => 16,
                'license_number' => 'BS-RE-2010-045',
                'avatar' => 'storage/avatars/2.jpg',
                'bio' => 'رائدة المشروعات السكنية والكمبوندات المغلقة والمباني الإدارية في مدينة بني سويف الجديدة بمحافظة بني سويف.',
                'bio_en' => 'Premier residential compound and commercial building developer in New Beni Suef City.',
            ],
            [
                'agency_name' => 'شركة المستقبل للاستثمار والتسويق العقاري',
                'agency_name_en' => 'Al Mostaqbal Real Estate',
                'slug' => 'al-mostaqbal-real-estate',
                'city' => 'مدينة بني سويف',
                'city_en' => 'Beni Suef City',
                'location_mark' => 'شارع عبد السلام عارف، بني سويف',
                'location_mark_en' => 'Abdelsalam Aref St, Beni Suef',
                'phone' => '01223456783',
                'whatsapp' => '201223456783',
                'email' => 'mostaqbal@darlii.eg',
                'job_title' => 'شركة وساطة واستشارات عقارية',
                'job_title_en' => 'Brokerage & Property Consultancy',
                'agent_name' => 'م. طارق رضوان المستقبلي',
                'agent_name_en' => 'Tarek Radwan',
                'years_experience' => 10,
                'license_number' => 'BS-RE-2016-112',
                'avatar' => 'storage/avatars/3.jpg',
                'bio' => 'خدمات تسويق ووساطة عقارية احترافية للوحدات السكنية والتجارية والأراضي في كافة مدن ومراكز بني سويف.',
                'bio_en' => 'Professional real estate marketing and advisory services across Beni Suef governorate.',
            ],
            [
                'agency_name' => 'شركة الأمل للتطوير العقاري بالواسطى',
                'agency_name_en' => 'Al Amal Real Estate El Wasta',
                'slug' => 'al-amal-real-estate-wasta',
                'city' => 'مركز الواسطى',
                'city_en' => 'El Wasta Center',
                'location_mark' => 'شارع أحمد عرابي، مركز الواسطى',
                'location_mark_en' => 'Ahmed Orabi St, El Wasta',
                'phone' => '01098765434',
                'whatsapp' => '201098765434',
                'email' => 'alamal@darlii.eg',
                'job_title' => 'وكالة عقارية معتمدة بالواسطى',
                'job_title_en' => 'Certified Property Agency in El Wasta',
                'agent_name' => 'أ. يوسف إبراهيم الواسطي',
                'agent_name_en' => 'Youssef Ibrahim',
                'years_experience' => 9,
                'license_number' => 'BS-RE-2017-094',
                'avatar' => 'storage/avatars/4.jpg',
                'bio' => 'الوجهة الأولى للراغبين في شراء وتأجير العقارات السكنية والتجارية والأراضي في مركز الواسطى ومحيطه بمحافظة بني سويف.',
                'bio_en' => 'Top property agency in El Wasta center and northern Beni Suef.',
            ],
            [
                'agency_name' => 'شركة هوم لاند للتسويق العقاري ببني سويف',
                'agency_name_en' => 'HomeLand Real Estate Beni Suef',
                'slug' => 'homeland-real-estate-beni-suef',
                'city' => 'مدينة بني سويف',
                'city_en' => 'Beni Suef City',
                'location_mark' => 'حي الزهراء، مدينة بني سويف',
                'location_mark_en' => 'El Zahraa District, Beni Suef',
                'phone' => '01145678905',
                'whatsapp' => '201145678905',
                'email' => 'homeland@darlii.eg',
                'job_title' => 'وكالة تسويق وإدارة أملاك',
                'job_title_en' => 'Marketing & Property Management',
                'agent_name' => 'أ. كريم حسني الزهراوي',
                'agent_name_en' => 'Karim Hosny',
                'years_experience' => 11,
                'license_number' => 'BS-RE-2015-067',
                'avatar' => 'storage/avatars/5.jpg',
                'bio' => 'متخصصون في أرقى أحياء بني سويف (الزهراء، مقبل، الأباصيري، وحي الجامعة)، تقديم استشارات عقارية مجانية ومعاينات فورية.',
                'bio_en' => 'Specialised in prime Beni Suef neighbourhoods: El Zahraa, Moqbel, and University District.',
            ],
            [
                'agency_name' => 'شركة الفشن للإسكان والتعمير',
                'agency_name_en' => 'El Fashn Housing & Development',
                'slug' => 'el-fashn-housing-development',
                'city' => 'مركز الفشن',
                'city_en' => 'El Fashn Center',
                'location_mark' => 'شارع البحر الأعظم، مركز الفشن',
                'location_mark_en' => 'El Bahr St, El Fashn',
                'phone' => '01278901236',
                'whatsapp' => '201278901236',
                'email' => 'fashn@darlii.eg',
                'job_title' => 'شركة إسكان وتطوير عقاري',
                'job_title_en' => 'Housing & Property Development',
                'agent_name' => 'م. مصطفى بدر الفشني',
                'agent_name_en' => 'Moustafa Badr',
                'years_experience' => 12,
                'license_number' => 'BS-RE-2014-053',
                'avatar' => 'storage/avatars/6.jpg',
                'bio' => 'خدمات تسويق وتطوير للمشروعات السكنية والتجارية والأراضي الزراعية والاستثمارية بمراكز الفشن وببا وسمسطا جنوب بني سويف.',
                'bio_en' => 'Residential housing and agricultural land development in southern Beni Suef.',
            ],
            [
                'agency_name' => 'شركة إهناسيا الحديثة للاستثمار العقاري',
                'agency_name_en' => 'Modern Ihnasya Real Estate',
                'slug' => 'modern-ihnasya-real-estate',
                'city' => 'مركز إهناسيا',
                'city_en' => 'Ihnasya Center',
                'location_mark' => 'شارع الجمهورية، مركز إهناسيا',
                'location_mark_en' => 'El Gomhoreya St, Ihnasya',
                'phone' => '01012345677',
                'whatsapp' => '201012345677',
                'email' => 'ihnasya@darlii.eg',
                'job_title' => 'شركة وساطة واستثمار بإهناسيا',
                'job_title_en' => 'Brokerage & Investment in Ihnasya',
                'agent_name' => 'أ. عمر الشريف الإهناسي',
                'agent_name_en' => 'Omar El Sherif',
                'years_experience' => 8,
                'license_number' => 'BS-RE-2018-121',
                'avatar' => 'storage/avatars/7.jpg',
                'bio' => 'وكالة عقارية معتمدة تقدم عروضاً حصرية للشقق والفيلات والأراضي بمركز إهناسيا والقرى التابعة لها.',
                'bio_en' => 'Certified real estate agency serving Ihnasya center and surrounding areas.',
            ],
            [
                'agency_name' => 'شركة ببا للاستثمار العقاري والمقاولات',
                'agency_name_en' => 'Biba Real Estate Investment',
                'slug' => 'biba-real-estate-investment',
                'city' => 'مركز ببا',
                'city_en' => 'Biba Center',
                'location_mark' => 'شارع التحرير، مركز ببا',
                'location_mark_en' => 'El Tahrir St, Biba',
                'phone' => '01033445568',
                'whatsapp' => '201033445568',
                'email' => 'biba@darlii.eg',
                'job_title' => 'شركة استثمار عقاري ومقاولات',
                'job_title_en' => 'Property Investment & Contracting',
                'agent_name' => 'م. خالد النجار البباوي',
                'agent_name_en' => 'Khaled El Naggar',
                'years_experience' => 10,
                'license_number' => 'BS-RE-2016-089',
                'avatar' => 'storage/avatars/8.jpg',
                'bio' => 'خبرة واسعة في تسويق المشروعات السكنية والأراضي التجارية بمركز ببا ومحافظة بني سويف.',
                'bio_en' => 'Trusted property broker and developer in Biba center, Beni Suef.',
            ],
            [
                'agency_name' => 'دارلي للوساطة العقارية - فرع بني سويف',
                'agency_name_en' => 'Darli Real Estate - Beni Suef Branch',
                'slug' => 'darli-beni-suef-branch',
                'city' => 'مدينة بني سويف',
                'city_en' => 'Beni Suef City',
                'location_mark' => 'شارع صلاح سالم، مدينة بني سويف',
                'location_mark_en' => 'Salah Salem St, Beni Suef City',
                'phone' => '01066778899',
                'whatsapp' => '201066778899',
                'email' => 'benisuef@darlii.eg',
                'job_title' => 'الفرع المعتمد لمنصة دارلي ببني سويف',
                'job_title_en' => 'Official Darli Branch in Beni Suef',
                'agent_name' => 'م. سامح كمال (دارلي بني سويف)',
                'agent_name_en' => 'Sameh Kamal',
                'years_experience' => 15,
                'license_number' => 'DARLI-EG-2024-001',
                'avatar' => 'storage/avatars/9.jpg',
                'bio' => 'الفرع المعتمد المباشر لمنصة دارلي العقارية في محافظة بني سويف، لخدمات البيع والشراء والتقييم العقاري المعتمد والمعاينة المجانية.',
                'bio_en' => 'Official direct Beni Suef branch of Darli Real Estate platform for verified buying, selling and valuation.',
            ],
        ];

        $allProperties = Property::query()->where('status', 'published')->get();
        $propChunkIndex = 0;
        $chunkSize = max(1, (int) ceil($allProperties->count() / count($companies)));

        foreach ($companies as $i => $data) {
            // 1. Create or update Agency
            $agency = Agency::firstOrNew(['slug' => $data['slug']]);
            $agency->fill([
                'name' => $data['agency_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'website' => 'https://darlii.eg/agencies/' . $data['slug'],
                'logo' => $data['avatar'],
                'description' => $data['bio'],
                'status' => 'active',
                'verified_at' => now(),
            ]);
            $agency->save();

            // 2. Create or update User for this agency / agent
            $user = User::firstOrNew(['email' => $data['email']]);
            $user->fill([
                'name' => $data['agency_name'],
                'phone' => $data['phone'],
                'password' => Hash::make('darli123456'),
                'job_title' => $data['job_title'],
                'status' => 'active',
                'avatar_path' => $data['avatar'],
                'preferred_locale' => 'ar',
                'email_verified_at' => now(),
            ]);
            $user->save();

            $user->roles()->syncWithoutDetaching([$agentRole->id]);
            $agency->users()->syncWithoutDetaching([$user->id => ['role' => 'manager']]);

            // 3. Create or update AgentProfile
            $profile = AgentProfile::firstOrNew(['slug' => $data['slug']]);
            $profile->fill([
                'user_id' => $user->id,
                'agency_id' => $agency->id,
                'job_title' => $data['job_title'],
                'license_number' => $data['license_number'],
                'years_experience' => $data['years_experience'],
                'whatsapp' => $data['whatsapp'],
                'website' => $agency->website,
                'avatar_path' => $data['avatar'],
                'bio' => $data['bio'],
                'status' => 'active',
                'verified_at' => now(),
            ]);
            $profile->save();

            // 4. Create translations for AgentProfile
            foreach (['ar' => $data['agency_name'], 'en' => $data['agency_name_en']] as $locale => $displayName) {
                $profile->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'display_name' => $displayName,
                        'bio' => $locale === 'ar' ? $data['bio'] : $data['bio_en'],
                        'seo_title' => $displayName . ' - بني سويف',
                        'seo_description' => Str::limit($locale === 'ar' ? $data['bio'] : $data['bio_en'], 160),
                    ]
                );
            }

            // 5. Assign a batch of properties to this agency user
            $assignedProps = $allProperties->slice($propChunkIndex * $chunkSize, $chunkSize);
            foreach ($assignedProps as $prop) {
                $prop->update(['owner_id' => $user->id]);
            }
            $propChunkIndex++;
        }

        $this->command?->info('Beni Suef real estate companies and agents seeded: ' . count($companies) . ' companies created.');
    }
}

