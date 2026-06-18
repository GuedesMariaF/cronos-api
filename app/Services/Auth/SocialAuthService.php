<?php

namespace App\Services\Auth;

use App\Exceptions\ApiException;
use App\Http\Resources\User\UserResource;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialAuthService
{
    public function redirectToGoogle(): string
    {
        return Socialite::driver('google')
            ->stateless()
            ->scopes(['openid', 'profile', 'email', 'https://www.googleapis.com/auth/calendar'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect()
            ->getTargetUrl();
    }

    public function handleGoogleCallback(): array
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            throw new ApiException('Falha ao autenticar com o Google.', 422);
        }

        $googleTokenData = [
            'google_access_token'    => $googleUser->token,
            'google_refresh_token'   => $googleUser->refreshToken,
            'google_token_expires_at' => Carbon::now()->addSeconds($googleUser->expiresIn),
        ];

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $updateData = $googleTokenData;
            if (! $user->google_id) {
                $updateData['google_id'] = $googleUser->getId();
            }
            // Só sobrescreve o refresh_token se o Google retornou um novo
            if (empty($googleUser->refreshToken)) {
                unset($updateData['google_refresh_token']);
            }
            $user->update($updateData);
        } else {
            $user = User::create([
                'name'     => $googleUser->getName(),
                'email'    => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => null,
                ...$googleTokenData,
            ]);

            $defaultRole = Role::first();
            if ($defaultRole) {
                $user->assignRole($defaultRole);
            }
        }

        $token = JWTAuth::fromUser($user);
        $user->load(['roles.permissions', 'roles']);
        $refreshTtlInSeconds = Config::get('jwt.refresh_ttl') * 60;

        return [
            'user'               => new UserResource($user),
            'token'              => $token,
            'refresh_expires_in' => $refreshTtlInSeconds,
        ];
    }

    public function getValidGoogleAccessToken(User $user): string
    {
        if ($user->google_token_expires_at && Carbon::now()->lt($user->google_token_expires_at)) {
            return $user->google_access_token;
        }

        if (! $user->google_refresh_token) {
            throw new ApiException('Sessão Google expirada. Faça login novamente.', 401);
        }

        $response = \Illuminate\Support\Facades\Http::post('https://oauth2.googleapis.com/token', [
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $user->google_refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        if (! $response->successful()) {
            throw new ApiException('Não foi possível renovar o token Google.', 401);
        }

        $data = $response->json();

        $user->update([
            'google_access_token'    => $data['access_token'],
            'google_token_expires_at' => Carbon::now()->addSeconds($data['expires_in']),
        ]);

        return $data['access_token'];
    }
}
