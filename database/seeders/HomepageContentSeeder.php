<?php

namespace Database\Seeders;

use App\Models\AgentProfile;
use App\Models\Location;
use App\Models\Post;
use App\Models\Property;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class HomepageContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedEgyptLocations();
        $this->seedTestimonials();
        $this->recoverFeaturedProperties();
        $this->recoverAgentAvatars();
        $this->recoverPostImages();
    }

    private function recoverFeaturedProperties(): void
    {
        $slugs = [
            'maple-grove-family-home',
            'whispering-pines-lodge',
            'briarwood-apartments',
            'summit-view',
            'elmwood-park',
            'rosewood-villas',
        ];

        Property::query()
            ->whereHas('translations', fn ($query) => $query->where('locale', 'en')->whereIn('slug', $slugs))
            ->update(['is_featured' => true, 'featured_until' => null]);
    }

    private function seedEgyptLocations(): void
    {
        foreach (config('egypt_map.governorates', []) as $key => $governorate) {
            $location = Location::updateOrCreate(
                ['type' => 'governorate', 'country_code' => 'EG', 'latitude' => $governorate['center'][0]],
                ['longitude' => $governorate['center'][1], 'status' => true]
            );

            foreach (config('locales.supported', ['en']) as $locale) {
                $location->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name' => $governorate['name'][$locale] ?? $governorate['name']['en'],
                        'slug' => $key,
                    ]
                );
            }
        }
    }

    private function seedTestimonials(): void
    {
        $items = [
            [
                'image' => 'storage/avatars/1.jpg',
                'en' => ['Jennifer Lee', 'Happy Home Seeker', 'From the initial consultation to closing day, the real estate team went above and beyond to ensure I found the perfect home. Their dedication and professionalism made the entire process seamless. Thank you!'],
                'ar' => ['السيدة جينيفر لي', 'باحث سعيد عن منزل', 'من الاستشارة الأولى حتى يوم الإغلاق، بذل فريق العقارات جهداً يفوق التوقعات لضمان أن أجد المنزل المثالي. جعلت تفانيهم واحترافيتهم العملية بأكملها سلسة. شكرًا لكم!'],
            ],
            [
                'image' => 'storage/avatars/10.jpg',
                'en' => ['Robert Evans', 'Property Investor', "I am impressed by the level of expertise and commitment demonstrated by this real estate team. Their insights into the market helped me make informed investment decisions, and I couldn't be happier with the results."],
                'ar' => ['السيد روبرت إيفانز', 'مستثمر عقاري', "I am impressed by the level of expertise and commitment demonstrated by this real estate team. Their insights into the market helped me make informed investment decisions, and I couldn't be happier with the results."],
            ],
            [
                'image' => 'storage/avatars/9.jpg',
                'en' => ['Jessica White', 'Delighted Home Seller', 'Selling my home with the help of this real estate team was a breeze. They provided valuable advice, staged my property beautifully, and negotiated a great deal. I highly recommend their services to anyone looking to sell their home!'],
                'ar' => ['السيدة جيسيكا وايت', 'بائع منزل سعيد', 'كان بيع منزلي بمساعدة هذا الفريق سهلاً للغاية. قدموا نصائح قيّمة، وجهزوا العقار بشكل رائع، وتفاوضوا على صفقة ممتازة. أوصي بخدماتهم بشدة لكل من يرغب في بيع منزله!'],
            ],
            [
                'image' => 'storage/avatars/11.jpg',
                'en' => ['Daniel Miller', 'Happy New Homeowner', 'Thanks to the expertise and guidance of this real estate team, I am now the proud owner of my dream home. They listened to my preferences, answered all my questions, and made the entire home buying process a positive experience.'],
                'ar' => ['السيد دانيال ميلر', 'مالك منزل جديد سعيد', 'بفضل خبرة وإرشاد هذا الفريق، أصبحت الآن المالك الفخور بمنزل أحلامي. استمعوا إلى تفضيلاتي، وأجابوا عن جميع أسئلتي، وجعلوا عملية شراء المنزل تجربة إيجابية.'],
            ],
        ];

        foreach ($items as $index => $item) {
            $testimonial = Testimonial::updateOrCreate(
                ['id' => $index + 1],
                ['image_path' => $item['image'], 'rating' => 5, 'status' => true, 'sort_order' => $index + 1]
            );

            foreach (['en', 'ar'] as $locale) {
                $testimonial->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['name' => $item[$locale][0], 'position' => $item[$locale][1], 'content' => $item[$locale][2]]
                );
            }
        }
    }

    private function recoverAgentAvatars(): void
    {
        AgentProfile::query()->each(function (AgentProfile $agent): void {
            $path = storage_path('app/legacy-site/en/agents/' . $agent->slug);
            if (! is_file($path)) {
                return;
            }

            if (preg_match('~storage/avatars/[^"\s]+~', file_get_contents($path), $match)) {
                $agent->forceFill(['avatar_path' => $match[0]])->saveQuietly();
            }
        });
    }

    private function recoverPostImages(): void
    {
        Post::query()->with('translations')->each(function (Post $post): void {
            $slug = $post->translations->firstWhere('locale', 'en')?->slug;
            $path = $slug ? storage_path('app/legacy-site/en/news/' . $slug) : null;
            if (! $path || ! is_file($path)) {
                return;
            }

            if (preg_match('~storage/posts/[^"\s]+~', file_get_contents($path), $match)) {
                $post->forceFill(['featured_image' => preg_replace('~-\d+x\d+(?=\.[a-z]+$)~i', '', $match[0])])->saveQuietly();
            }
        });
    }
}
