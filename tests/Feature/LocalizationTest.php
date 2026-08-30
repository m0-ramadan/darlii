<?php
namespace Tests\Feature; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class LocalizationTest extends TestCase {use RefreshDatabase; public function test_supported_locale_home_works():void{$this->seed();$this->get('/ar')->assertOk()->assertSee('dir="rtl"',false);}}
