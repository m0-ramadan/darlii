<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentPagesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPrivacyPolicy();
        $this->seedFaqs();
        $this->seedContactPage();
        $this->seedCareers();
    }

    private function seedPrivacyPolicy(): void
    {
        $page = Page::firstOrNew(['key' => 'privacy-policy']);
        $page->fill([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $page->save();

        $page->translations()->updateOrCreate(
            ['locale' => 'ar'],
            [
                'title' => 'سياسة الخصوصية وحماية البيانات',
                'slug' => 'privacy-policy',
                'seo_title' => 'سياسة الخصوصية | منصة دارلي العقارية',
                'seo_description' => 'تعرف على سياسة الخصوصية وضوابط حماية بيانات العملاء والمستخدمين على منصة دارلي العقارية.',
                'content' => '<h3>مقدمة والتزامنا بالخصوصية</h3>
<p>مرحباً بكم في منصة <strong>دارلي العقارية (Darli Real Estate)</strong>. نحن نلتزم التزاماً كاملاً بحماية خصوصية وأمان بياناتك الشخصية عند استخدامك لموقعنا الإلكتروني وتطبيقاتنا وخدمات الوساطة والاستثمار العقاري.</p>

<h3>1. المعلومات والبيانات التي نقوم بجمعها</h3>
<p>نقوم بجمع البيانات اللازمة لتقديم أفضل تجربة عقارية متكاملة ومخصصة لاحتياجاتك، وتشمل:</p>
<ul>
    <li><strong>بيانات الهوية والتواصل:</strong> الاسم الكامل، رقم الهاتف، رقم الواتساب، والبريد الإلكتروني عند تسجيل الحساب أو إرسال استفسار.</li>
    <li><strong>بيانات الاهتمامات العقارية:</strong> نوع العقار المطلوب (شقة، فيلا، أرض، تجاري)، المدينة أو المركز المفضل في بني سويف، ونطاق السعر والميزانية.</li>
    <li><strong>بيانات الوكلاء والمطورين:</strong> التراخيص العقارية، بيانات الشركات، وعناوين الفروع للشركات المسجلة في دليل وكلائنا.</li>
    <li><strong>بيانات التصفح والاستخدام:</strong> عنوان IP، نوع المتصفح، الصفحات التي قمت بزيارتها، وتفضيلات اللغة والمظهر (الوضع الليلي).</li>
</ul>

<h3>2. كيف نستخدم معلوماتك</h3>
<p>تُستخدم بياناتك للأغراض التالية فقط وبأعلى معايير الشفافية:</p>
<ul>
    <li>ربطك بالوكلاء والشركات العقارية المعتمدة لتنسيق المعاينات والإجابة عن استفساراتك.</li>
    <li>تخصيص نتائج البحث وإظهار العقارات الأنسب لميزانيتك وموقعك الجغرافي.</li>
    <li>إرسال تنبيهات بالعقارات الجديدة والمشروعات المميزة في بني سويف ومصر.</li>
    <li>تطوير أداء المنصة وتحسين سرعة التصفح وتأمين المعاملات العقارية.</li>
</ul>

<h3>3. ملفات تعريف الارتباط (Cookies) والتفضيلات</h3>
<p>نستخدم ملفات الكوكيز لحفظ تفضيلاتك الشخصية مثل العملة المفضلة (جنيه مصري / دولار)، تفضيل اللغة (العربية / الإنجليزية)، ووضع المظهر المفضل (فاتح / داكن) دون الحاجة لإعادة ضبطها في كل زيارة.</p>

<h3>4. أمن البيانات وحمايتها</h3>
<p>نطبق بروتوكولات تشفير متقدمة (SSL/TLS) وقواعد أمنية صارمة تمنع الوصول غير المصرح به لمعلوماتك، ولا نقوم ببيع أو تأجير بياناتك لأي أطراف تجارية ثالثة على الإطلاق.</p>

<h3>5. حقوق المستخدم والتحكم في البيانات</h3>
<p>يحق لك في أي وقت مراجعة بياناتك المسجلة لدينا، تعديلها، أو طلب حذف حسابك وبياناتك نهائياً من خلال التواصل معنا عبر البريد الإلكتروني المخصص للخصوصية: <strong>privacy@darlii.eg</strong>.</p>',
            ]
        );

        $page->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'title' => 'Privacy & Data Protection Policy',
                'slug' => 'privacy-policy-en',
                'seo_title' => 'Privacy Policy | Darli Real Estate Platform',
                'seo_description' => 'Learn how Darli protects your privacy and personal data when using our real estate platform and services.',
                'content' => '<h3>Introduction & Commitment</h3>
<p>Welcome to <strong>Darli Real Estate Platform</strong>. We are committed to safeguarding your privacy and ensuring your personal information is handled securely and transparently.</p>

<h3>1. Information We Collect</h3>
<ul>
    <li><strong>Contact Data:</strong> Full name, phone number, WhatsApp, and email address.</li>
    <li><strong>Property Preferences:</strong> Property types, preferred markazes/cities in Beni Suef, and budget ranges.</li>
    <li><strong>Agent & Developer Data:</strong> Professional licenses, agency names, and branch addresses.</li>
    <li><strong>Usage & Navigation:</strong> Device information, preferred currency, language, and theme mode.</li>
</ul>

<h3>2. How We Use Your Data</h3>
<p>Your information is used solely to match you with vetted real estate advisors, arrange viewings, and enhance your property discovery experience.</p>

<h3>3. Security & Your Rights</h3>
<p>We implement end-to-end encryption protocols and do not sell or rent user data. You have the right to request access, correction, or deletion of your information by contacting <strong>privacy@darlii.eg</strong>.</p>',
            ]
        );
    }

    private function seedFaqs(): void
    {
        $faqsData = [
            [
                'category' => 'الشراء والاستثمار العقاري',
                'category_en' => 'Buying & Property Investment',
                'question' => 'كيف أضمن أمان المعاملة القانونية عند شراء عقار عبر دارلي؟',
                'question_en' => 'How can I ensure legal safety when buying a property on Darli?',
                'answer' => 'جميع العقارات والشركات المعروضة على منصة دارلي تخضع لفحص دقيق لصحيفة التراخيص وسندات الملكية. كما نوفر خدمة فحص قانوني مجانية ومستشارين معتمدين لمراجعة عقود البيع وتسلسل الملكية قبل دفع أي مقدم.',
                'answer_en' => 'All properties and listed agencies undergo strict verification of legal titles and building permits. We also provide legal contract review support before any payment is made.',
            ],
            [
                'category' => 'الشراء والاستثمار العقاري',
                'category_en' => 'Buying & Property Investment',
                'question' => 'ما هي أفضل المناطق الواعدة للاستثمار العقاري في بني سويف حالياً؟',
                'question_en' => 'What are the most promising investment areas in Beni Suef right now?',
                'answer' => 'تعد مدينة بني سويف الجديدة (خاصة الحي الثاني والحي الثالث) والمنطقة المحيطة بكورنيش النيل وشارع عبد السلام عارف من أعلى المناطق نمواً في القيمة الإيجارية والاستثمارية، بالإضافة لمشروعات التوسعات العمرانية بالواسطى وببا.',
                'answer_en' => 'New Beni Suef City (Districts 2 & 3), Nile Corniche area, and Abdelsalam Aref Street have the highest ROI and rental demand in the governorate.',
            ],
            [
                'category' => 'الإيجار والوحدات السكنية',
                'category_en' => 'Renting & Housing Units',
                'question' => 'هل تتوفر شقق مفروشة ووحدات إدارية للإيجار في مراكز المحافظة؟',
                'question_en' => 'Are furnished apartments and commercial spaces available across Beni Suef centers?',
                'answer' => 'نعم، يوفر دليل دارلي وحدات سكنية مفروشة ومكاتب وعيادات ومحلات تجارية للإيجار في مدينة بني سويف وكافة المراكز (الواسطى، ناصر، ببا، الفشن، إهناسيا، سمسطا) مع إمكانية المعاينة الفورية.',
                'answer_en' => 'Yes, our catalog includes fully furnished apartments, offices, clinics, and commercial stores for rent across all Beni Suef markazes with instant viewing scheduling.',
            ],
            [
                'category' => 'الوكلاء والشركات المعتمدة',
                'category_en' => 'Certified Agents & Agencies',
                'question' => 'ماذا تعني شارة "معتمد" الممنوحة للوكلاء والشركات في دارلي؟',
                'question_en' => 'What does the "Verified" badge on agencies and agents mean?',
                'answer' => 'شارة "معتمد" تعني أن الشركة أو الوكيل مسجل رسمياً بسجل تجاري وترخيص وساطة عقارية سارٍ، وتم التحقق من هويته ومقره الميداني وتاريخه المهني والتزامه بميثاق الشفافية والأسعار الحقيقية.',
                'answer_en' => 'The "Verified" badge confirms the agency possesses a valid commercial license and verified physical office, ensuring zero phantom listings and strict pricing transparency.',
            ],
            [
                'category' => 'التمويل والدفع والأقساط',
                'category_en' => 'Financing & Payment Plans',
                'question' => 'هل تتوفر وحدات تدعم أنظمة التمويل العقاري أو التقسيط المباشر؟',
                'question_en' => 'Are there properties eligible for mortgage financing or developer installments?',
                'answer' => 'نعم، لدينا قسم مخصص للوحدات المطابقة لمبادرة التمويل العقاري للبنك المركزي (بفائدة 3% و 8%) بالإضافة إلى مشروعات المطوّرين العقاريين التي توفر خطط تقسيط مرنة تصل حتى 7 و 10 سنوات بدون فوائد.',
                'answer_en' => 'Yes, we have units eligible for Egyptian Central Bank mortgage initiatives (3% and 8%) as well as developer projects with zero-interest installment plans up to 7-10 years.',
            ],
            [
                'category' => 'الإعلان وإضافة العقارات',
                'category_en' => 'Listing & Selling Properties',
                'question' => 'كيف يمكنني كمالك أو وسيط عقاري إضافة عقاراتي على منصة دارلي؟',
                'question_en' => 'How can I list my property as an owner or broker on Darli?',
                'answer' => 'يمكنك الضغط على زر "إضافة عقار" في أعلى الصفحة وتسجيل حسابك، ثم إدخال تفاصيل الوحدة والصور والموقع الجغرافي بدقة. يقوم فريق المراجعة بتدقيق الطلب ونشره فوراً أمام آلاف المشترين.',
                'answer_en' => 'Click the "Add Property" button in the header, sign in, enter your unit specs, photos and location. Our moderation team reviews and publishes it promptly.',
            ],
        ];

        $page = Page::firstOrNew(['key' => 'faqs']);
        $page->fill([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $page->save();

        $page->translations()->updateOrCreate(
            ['locale' => 'ar'],
            [
                'title' => 'الأسئلة الشائعة والأجوبة الوافية',
                'slug' => 'faqs',
                'seo_title' => 'الأسئلة الشائعة | منصة دارلي العقارية',
                'seo_description' => 'إجابات على كافة الأسئلة المتعلقة بشراء وتأجير العقارات والاستثمار والتمويل العقاري في بني سويف ومصر.',
                'content' => json_encode($faqsData, JSON_UNESCAPED_UNICODE),
            ]
        );

        $page->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'title' => 'Frequently Asked Questions (FAQ)',
                'slug' => 'faqs-en',
                'seo_title' => 'Frequently Asked Questions | Darli Real Estate',
                'seo_description' => 'Answers to common questions regarding buying, renting, financing, and verified brokers in Beni Suef and Egypt.',
                'content' => json_encode($faqsData, JSON_UNESCAPED_UNICODE),
            ]
        );
    }

    private function seedContactPage(): void
    {
        $page = Page::firstOrNew(['key' => 'contact-us']);
        $page->fill([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $page->save();

        $page->translations()->updateOrCreate(
            ['locale' => 'ar'],
            [
                'title' => 'تواصل معنا - منصة دارلي العقارية',
                'slug' => 'contact-us',
                'seo_title' => 'اتصل بنا | منصة دارلي العقارية',
                'seo_description' => 'تواصل مع فريق منصة دارلي العقارية للاستفسارات والمعاينات وخدمات الاستثمار العقاري ببني سويف.',
                'content' => 'يسعدنا دائماً استقبال استفساراتكم ومساعدتكم في اختيار عقاركم المثالي بمحافظة بني سويف.',
            ]
        );

        $page->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'title' => 'Contact Us - Darli Real Estate Platform',
                'slug' => 'contact-us-en',
                'seo_title' => 'Contact Us | Darli Real Estate',
                'seo_description' => 'Get in touch with Darli Real Estate team for property consultations, viewings, and investment inquiries.',
                'content' => 'We are always glad to assist you in discovering the ideal property across Beni Suef and Egypt.',
            ]
        );

        // Update settings with realistic Beni Suef office details
        app(\App\Services\Settings\SettingsRepository::class)->setMany([
            'contact.address_ar' => 'شارع صلاح سالم (تقاطع كورنيش النيل)، مدينة بني سويف، محافظة بني سويف',
            'contact.address_en' => 'Salah Salem St (Nile Corniche intersection), Beni Suef City, Egypt',
            'contact.phone' => '+20 10 34905812',
            'contact.whatsapp' => '+20 10 34905812',
            'contact.email' => 'contact@darlii.eg',
        ]);
    }

    private function seedCareers(): void
    {
        $jobs = [
            [
                'slug' => 'senior-real-estate-sales-advisor',
                'department' => 'المبيعات والاستثمار',
                'location' => 'مدينة بني سويف',
                'employment_type' => 'دوام كامل',
                'experience_level' => 'متوسط / خبير',
                'salary_min' => 12000,
                'salary_max' => 25000,
                'deadline' => now()->addMonths(2),
                'ar' => [
                    'title' => 'مستشار مبيعات واستثمار عقاري أول',
                    'summary' => 'مطلوب مستشار مبيعات عقارية متميز للتواصل مع كبار العملاء والمستثمرين وتقديم استشارات شراء للمشروعات السكنية والتجارية في بني سويف.',
                    'description' => "المسؤوليات:\n- استقبال العملاء والرد على استفساراتهم وتقديم العروض الاستثمارية المناسبة لاحتياجاتهم وميزانياتهم.\n- تنظيم وإجراء جولات المعاينة الميدانية للوحدات السكنية والتجارية والأراضي.\n- متابعة مراحل التفاوض وإتمام التعاقدات وفق النماذج القانونية المعتمدة.\n- بناء شبكة علاقات قوية مع كبار المستثمرين والمطورين العقاريين.\n\nالمتطلبات:\n- خبرة لا تقل عن 3 سنوات في مجال المبيعات والوساطة العقارية.\n- إجادة تامة لمهارات الإقناع والتفاوض وخدمة العملاء.\n- معرفة شاملة بالمناطق السكنية والاستثمارية بمحافظة بني سويف.\n- مهارات اتصال ممتازة وحضور مهني راقٍ.",
                ],
                'en' => [
                    'title' => 'Senior Real Estate Investment Advisor',
                    'summary' => 'Seeking an accomplished real estate sales consultant to guide buyers and investors towards premium residential and commercial assets in Beni Suef.',
                    'description' => "Responsibilities:\n- Meet prospective buyers and evaluate their investment requirements.\n- Arrange and host physical viewings across prime developments.\n- Negotiate deals and support contract signing.\n\nRequirements:\n- 3+ years experience in property brokerage.\n- Strong negotiation and communication skills.\n- Deep knowledge of Beni Suef property market.",
                ],
            ],
            [
                'slug' => 'property-valuation-engineer',
                'department' => 'التقييم والتسعير الهندسي',
                'location' => 'بني سويف / العمل الميداني',
                'employment_type' => 'دوام كامل',
                'experience_level' => 'خبير',
                'salary_min' => 15000,
                'salary_max' => 28000,
                'deadline' => now()->addMonths(3),
                'ar' => [
                    'title' => 'مهندس تقييم عقاري وتسعير معتمد',
                    'summary' => 'مهندس مدني / معماري متخصص في التقييم الهندسي والفحص الإنشائي للوحدات العقارية وإعداد تقارير القيمة العادلة المعتمدة.',
                    'description' => "المسؤوليات:\n- إجراء المعاينات الهندسية والفحص الإنشائي للوحدات والمباني السكنية والتجارية.\n- دراسة الموقع وسعر المتر ومؤشرات السوق لتحديد القيمة السوقية العادلة بدقة.\n- إعداد تقارير التقييم العقاري المعتمدة لعملاء المنصة والبنوك الشريكة.\n- التأكد من مطابقة المباني للتراخيص الهندسية الصادرة.\n\nالمتطلبات:\n- بكالوريوس هندسة مدنية أو عمارة.\n- خبرة 3-5 سنوات في التقييم العقاري والفحص الفني.\n- عضوية نقابة المهندسين المصرية وشهادة مقيم عقاري معتمد ميزة إضافية.",
                ],
                'en' => [
                    'title' => 'Certified Property Valuation Engineer',
                    'summary' => 'Civil/Architectural engineer specialised in structural inspection and market valuation for residential and commercial assets.',
                    'description' => "Responsibilities:\n- Conduct site technical inspections of properties.\n- Analyze market benchmarks to determine fair market value.\n- Issue certified valuation reports.",
                ],
            ],
            [
                'slug' => 'digital-real-estate-marketer',
                'department' => 'التسويق الرقمي والإعلام',
                'location' => 'مدينة بني سويف (هجين)',
                'employment_type' => 'دوام كامل',
                'experience_level' => 'متوسط',
                'salary_min' => 10000,
                'salary_max' => 18000,
                'deadline' => now()->addMonths(2),
                'ar' => [
                    'title' => 'أخصائي تسويق رقمي وحملات عقارية',
                    'summary' => 'مطلوب متخصص في إدارة الحملات الإعلانية الممولة (Facebook, Google, TikTok) وصناعة المحتوى العقاري الجذاب.',
                    'description' => "المسؤوليات:\n- تخطيط وإدارة الحملات الإعلانية الممولة للمشروعات العقارية وجلب العملاء المحتملين (Lead Generation).\n- إنتاج محتوى مرئي وجذاب للمشروعات العقارية عبر منصات التواصل الاجتماعي.\n- تحليل أداء الحملات وتحسين معدلات التحويل وخفض تكلفة العميل.\n\nالمتطلبات:\n- خبرة موثقة في الحملات الرقمية للقطاع العقاري.\n- إجادة التعامل مع Meta Ads Manager و Google Ads وأدوات التحليل.\n- مهارات كتابة إعلانية (Copywriting) مؤثرة.",
                ],
                'en' => [
                    'title' => 'Digital Real Estate Marketing Specialist',
                    'summary' => 'Paid media specialist to execute lead generation campaigns across Meta, Google, and video channels.',
                    'description' => "Responsibilities:\n- Plan and optimize real estate lead generation campaigns.\n- Craft engaging property content.\n- Maximize ROI and conversion rates.",
                ],
            ],
            [
                'slug' => 'real-estate-legal-counsel',
                'department' => 'الشؤون القانونية والعقود',
                'location' => 'مدينة بني سويف',
                'employment_type' => 'دوام جزئي / استشاري',
                'experience_level' => 'خبير',
                'salary_min' => 14000,
                'salary_max' => 22000,
                'deadline' => now()->addMonths(3),
                'ar' => [
                    'title' => 'مستشار قانوني وتوثيق عقود عقارية',
                    'summary' => 'محامٍ مقيد بالاستئناف متخصص في فحص الملكيات العقارية وتوثيق عقود البيع والشراء وإجراءات الشهر العقاري.',
                    'description' => "المسؤوليات:\n- مراجعة تسلسل ملكية العقارات والأراضي والتأكد من خلوها من أي موانع قانونية أو نزاعات.\n- صياغة وتدقيق عقود البيع والشراء والإيجار والاتفاقيات بين الأطراف.\n- توجيه العملاء في إجراءات التسجيل بالشهر العقاري ودعاوى صحة ونفاذ العقد.\n\nالمتطلبات:\n- ليسانس حقوق مع قيد نقابي سارٍ (استئناف على الأقل).\n- خبرة عملية واسعة في قضايا العقارات والشهر العقاري بمحافظة بني سويف.\n- دقة عالية وأمانة مهنية.",
                ],
                'en' => [
                    'title' => 'Real Estate Legal Counsel',
                    'summary' => 'Corporate property attorney experienced in title deeds verification and real estate contract conveyance.',
                    'description' => "Responsibilities:\n- Verify title deeds chain and clearance.\n- Draft and audit purchase and lease agreements.\n- Guide clients through land registry protocols.",
                ],
            ],
        ];

        foreach ($jobs as $data) {
            $career = Career::firstOrNew([
                'department' => $data['department'],
                'location' => $data['location'],
            ]);

            $career->fill([
                'employment_type' => $data['employment_type'],
                'experience_level' => $data['experience_level'],
                'salary_min' => $data['salary_min'],
                'salary_max' => $data['salary_max'],
                'deadline' => $data['deadline'],
                'status' => 'published',
            ]);
            $career->save();

            foreach (['ar', 'en'] as $locale) {
                $career->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $data[$locale]['title'],
                        'slug' => $locale === 'ar' ? $data['slug'] . '-ar' : $data['slug'],
                        'summary' => $data[$locale]['summary'],
                        'description' => $data[$locale]['description'],
                        'seo_title' => $data[$locale]['title'] . ' | وظائف دارلي',
                        'seo_description' => Str::limit($data[$locale]['summary'], 160),
                    ]
                );
            }
        }
    }
}
