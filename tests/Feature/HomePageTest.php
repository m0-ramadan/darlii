<?php
namespace Tests\Feature; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class HomePageTest extends TestCase {use RefreshDatabase; public function test_default_redirects_to_arabic():void{$this->get('/')->assertRedirect('/ar');}}
