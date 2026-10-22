<?php

namespace App\Console\Commands;

use App\Services\Seo\SitemapGenerator;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'توليد/تحديث ملف sitemap.xml من محتوى الموقع';

    public function handle(SitemapGenerator $generator): int
    {
        $count = $generator->write();

        if ($count === 0) {
            $this->warn('لم يتم توليد خريطة الموقع (قد تكون غير مفعّلة من الإعدادات أو تعذّر الكتابة).');

            return self::FAILURE;
        }

        $this->info("تم تحديث sitemap.xml بعدد {$count} رابطًا.");

        return self::SUCCESS;
    }
}
