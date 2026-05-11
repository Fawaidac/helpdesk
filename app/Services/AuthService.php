<?php
namespace App\Services;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function login($username, $password)
    {
        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Username atau password salah');
        }

        ApiToken::where('user_id', $user->id)->delete();

        $token = base64_encode(Str::random(100));

        ApiToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expired_at' => now()->addDays(7)
        ]);

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function getUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('User tidak ditemukan');
        }

        return $user;
    }
}