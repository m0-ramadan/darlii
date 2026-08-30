<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExactFrontendTest extends TestCase
{
    public function test_english_home_uses_original_homzen_frontend(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('themes/homzen/css/style.css?v=1.3.3', false)
            ->assertSee('id="page-4"', false)
            ->assertSee('themes/homzen/css/dark-mode.css', false);
    }

    public function test_arabic_home_keeps_original_rtl_frontend(): void
    {
        $this->get('/ar')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('themes/homzen/css/rtl-custom.css', false);
    }

    public function test_original_property_detail_is_served(): void
    {
        $this->get('/en/properties/private-estate-magnificent-views')
            ->assertOk()
            ->assertSee('Private Estate Magnificent Views', false)
            ->assertSee('themes/homzen/css/style.css?v=1.3.3', false);
    }
}
