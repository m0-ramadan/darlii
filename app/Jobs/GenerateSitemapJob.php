<?php

namespace App\Jobs;

use App\Services\Seo\SitemapGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSitemapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(SitemapGenerator $generator): void
    {
        try {
            $generator->write();
        } catch (\Throwable $e) {
            Log::warning('Sitemap generation failed: ' . $e->getMessage());
        }
    }
}
