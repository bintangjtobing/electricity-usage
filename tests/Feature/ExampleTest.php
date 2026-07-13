<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_sends_guests_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
