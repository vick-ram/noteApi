<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class JwtGuard implements Guard
{
    protected $request;
    protected $provider;
    protected $user;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
        $this->user = null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        $token = $this->getTokenFromRequest();
        if (!$token) {
            return response()->json([
                'success' => false,
                'error' => 'Token not provided'
            ], 401);
        }

        $payload = $this->decodeToken($token);
        if (!isset($payload['sub'])) {
            return response()->json([
                'success' => false,
                'error' => 'Token does not contain subject (sub) claim'
            ], 401);
        }
        $user =
            $this->provider->retrieveById($payload['sub']);

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'User not found'
            ], 401);
        }

        $this->user = $user;

        return $this->user;
    }

    public function id()
    {
        $user = $this->user();
        return $user ? $user->getAuthIdentifier() : null;
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function validate(array $credentials = []): bool
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            return false;
        }

        $user = $this->provider->retrieveByCredentials($credentials);
        if (!$user) {
            return false;
        }

        return $this->provider->validateCredentials($user, $credentials);
    }

    protected function getTokenFromRequest()
    {
        return $this->request->bearerToken();
    }

    protected function decodeToken(string $token): ?array
    {
        try {
            $payload = json_decode(base64_decode(explode('.', $token)[1]), true);

            $user = User::find($payload['sub']);

            if (!$user) {
                return null;
            }

            $issuedAt = $payload['iat'] ?? 0;

            if ($user->last_issued_at && $issuedAt < $user->last_issued_at->timestamp) {
                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            return null;
        }
    }
}
