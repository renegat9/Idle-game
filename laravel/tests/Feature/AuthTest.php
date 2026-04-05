<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── Register ────────────────────────────────────────────────────────────

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username'              => 'Gruntak',
            'email'                 => 'gruntak@donjon.fr',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['token', 'user' => ['id', 'username', 'gold']]);

        $this->assertDatabaseHas('users', ['username' => 'Gruntak']);
    }

    public function test_register_requires_all_fields(): void
    {
        $this->postJson('/api/auth/register', [])->assertStatus(422);
    }

    public function test_register_username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'Gruntak']);

        $this->postJson('/api/auth/register', [
            'username'              => 'Gruntak',
            'email'                 => 'autre@donjon.fr',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertStatus(422);
    }

    public function test_new_user_starts_with_gold(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username'              => 'Fizzle',
            'email'                 => 'fizzle@donjon.fr',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201);
        $this->assertGreaterThan(0, $response->json('user.gold'));
    }

    // ─── Login ───────────────────────────────────────────────────────────────

    public function test_login_returns_token(): void
    {
        User::factory()->create([
            'email'    => 'test@donjon.fr',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@donjon.fr',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_wrong_password_returns_401(): void
    {
        User::factory()->create(['email' => 'test@donjon.fr']);

        $this->postJson('/api/auth/login', [
            'email'    => 'test@donjon.fr',
            'password' => 'mauvais_mot_de_passe',
        ])->assertStatus(401);
    }

    public function test_login_deletes_existing_tokens_single_session(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@donjon.fr',
            'password' => bcrypt('secret123'),
        ]);

        // Create an old token
        $user->createToken('old-session');
        $this->assertCount(1, $user->tokens);

        // Login again
        $this->postJson('/api/auth/login', [
            'email'    => 'test@donjon.fr',
            'password' => 'secret123',
        ])->assertStatus(200);

        // Only 1 token should exist (the new one)
        $this->assertCount(1, $user->fresh()->tokens);
    }

    // ─── Logout ──────────────────────────────────────────────────────────────

    public function test_logout_deletes_token(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('session')->plainTextToken;

        $this->withToken($token)
             ->postJson('/api/auth/logout')
             ->assertStatus(200);

        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_protected_route_requires_auth(): void
    {
        $this->getJson('/api/game/dashboard')->assertStatus(401);
    }
}
