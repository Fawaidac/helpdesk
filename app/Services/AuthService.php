<?php
namespace App\Services;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function login(string $username, string $password): array
    {
        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Username atau password salah');
        }

        // ApiToken::where('user_id', $user->id)->delete();

        $token = base64_encode(Str::random(100));

        ApiToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expired_at' => now()->addDays(3)
        ]);

        return [
            // 'user' => $user,
            'token' => $token
        ];
    }

    public function getUser(int $userId): User
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('User tidak ditemukan');
        }

        return $user;
    }

    public function checkPin(int $userId, string $pin): bool
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('User tidak ditemukan');
        }

        if (!$user->pin) {
            throw new \Exception('User tidak memiliki PIN');
        }

        if (!Hash::check($pin, $user->pin)) {
            throw new \Exception('PIN salah');
        }

        return true;
    }
}