<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_existing_user_can_sign_in_with_phone_number()
    {
        $user = User::factory()->create([
            'phone_number' => '+213555123456',
        ]);

        $response = $this->post(route('login.store'), [
            'phone_number' => $user->phone_number,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_non_existing_phone_number_creates_user_and_signs_in()
    {
        $response = $this->post(route('login.store'), [
            'phone_number' => '+213777888999',
        ]);

        $this->assertDatabaseHas('users', [
            'phone_number' => '+213777888999',
        ]);
        $this->assertDatabaseHas('trust_checks', [
            'phone_number' => '+213777888999',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_phone_number_must_be_in_e164_format()
    {
        $response = $this->from(route('login'))->post(route('login.store'), [
            'phone_number' => 'invalid-phone',
        ]);

        $response->assertSessionHasErrors('phone_number');
        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create([
            'phone_number' => '+213000000001',
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
