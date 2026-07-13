<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider protectedPages */
    public function test_pages_require_login(string $path): void
    {
        $this->get($path)->assertRedirect('/login');
    }

    public static function protectedPages(): array
    {
        return [
            ['/dashboard'],
            ['/purchase'],
            ['/check'],
            ['/history'],
            ['/settings'],
        ];
    }

    /** @dataProvider protectedPages */
    public function test_logged_in_user_can_open_every_page(string $path): void
    {
        $this->actingAs(User::factory()->create())
            ->get($path)
            ->assertOk();
    }

    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create(['email' => 'a@b.com']);

        $this->post('/login', ['email' => 'a@b.com', 'password' => 'salah-sekali'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_succeeds_with_correct_password(): void
    {
        $user = User::factory()->create([
            'email' => 'a@b.com',
            'password' => bcrypt('rahasia-kuat-123'),
        ]);

        $this->post('/login', ['email' => 'a@b.com', 'password' => 'rahasia-kuat-123'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }
}
