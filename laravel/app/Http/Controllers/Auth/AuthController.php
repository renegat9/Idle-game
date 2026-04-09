<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.required' => 'Le pseudo est obligatoire.',
            'username.unique' => 'Ce pseudo est déjà pris.',
            'username.regex' => 'Le pseudo ne peut contenir que des lettres, chiffres et underscores.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.min' => 'Le mot de passe doit faire au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'gold' => 100,
            'level' => 1,
            'xp' => 0,
            'xp_to_next_level' => 100,
        ]);

        $token = $user->createToken('game-session')->plainTextToken;

        return response()->json([
            'message' => 'Compte créé. Maintenant crée un héros, le Narrateur te surveille.',
            'token' => $token,
            'user' => $this->userResponse($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'L\'email est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects. Le Narrateur lève les yeux au ciel.',
            ], 401);
        }

        // Session unique : supprimer tous les tokens existants
        $user->tokens()->delete();

        $token = $user->createToken('game-session')->plainTextToken;

        $user->touch();

        return response()->json([
            'message' => 'Bienvenue, ' . $user->username . '. Le Narrateur était presque content de te voir.',
            'token' => $token,
            'user' => $this->userResponse($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnecté. Le Narrateur peut enfin se reposer.',
        ]);
    }

    private function userResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'gold' => $user->gold,
            'level' => $user->level,
            'xp' => $user->xp,
            'xp_to_next_level' => $user->xp_to_next_level,
            'narrator_frequency' => $user->narrator_frequency,
        ];
    }
}
