<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_home_renders_arabic_frontend(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('dir="rtl"', false);
    }

    public function test_home_respects_the_saved_english_locale(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertOk()
            ->assertSee('dir="ltr"', false);
    }
}
