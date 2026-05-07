<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    private const ALLOWED_PROVIDERS = ['github', 'google'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::ALLOWED_PROVIDERS), 404);

        try {
            $socialiteUser = Socialite::driver($provider)->user();

            $user = User::where('provider', $provider)
                ->where('provider_id', $socialiteUser->getId())
                ->first();

            if ($user) {
                Auth::login($user);
                return redirect('/dashboard');
            }

            $user = User::create([
                'name'        => $socialiteUser->getName() ?? $socialiteUser->getNickname() ?? 'User',
                'email'       => $socialiteUser->getEmail(),
                'avatar'      => $socialiteUser->getAvatar(),
                'provider'    => $provider,
                'provider_id' => $socialiteUser->getId(),
                'password'    => bcrypt(Str::random(32)),
            ]);

            Auth::login($user);

            return redirect('/dashboard');

        } catch (\Throwable $e) {
            return redirect('/login')->withErrors([
                'email' => 'Anmeldung fehlgeschlagen. Bitte erneut versuchen.',
            ]);
        }
    }
}
