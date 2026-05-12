<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

            // Returning user — just log in
            $user = User::where('provider', $provider)
                ->where('provider_id', $socialiteUser->getId())
                ->first();

            if ($user) {
                Auth::login($user);
                return $this->redirectAfterLogin($user);
            }

            // New user — determine waitlist status atomically
            $user = DB::transaction(function () use ($provider, $socialiteUser) {
                $approvedCount = User::where('waitlist_status', 'approved')
                    ->lockForUpdate()
                    ->count();

                $isFree     = $approvedCount < User::FREE_TIER_LIMIT;
                $status     = $isFree ? 'approved' : 'waiting';
                $approvedAt = $isFree ? now() : null;

                return User::create([
                    'name'            => $socialiteUser->getName() ?? $socialiteUser->getNickname() ?? 'User',
                    'email'           => $socialiteUser->getEmail(),
                    'avatar'          => $socialiteUser->getAvatar(),
                    'provider'        => $provider,
                    'provider_id'     => $socialiteUser->getId(),
                    'password'        => bcrypt(Str::random(32)),
                    'scans_limit'     => 100,
                    'waitlist_status' => $status,
                    'is_free_tier'    => $isFree,
                    'approved_at'     => $approvedAt,
                ]);
            });

            Mail::to($user->email)->queue(new WelcomeMail($user));
            Auth::login($user);

            return $this->redirectAfterLogin($user);

        } catch (\Throwable $e) {
            return redirect('/login')->withErrors([
                'email' => 'Anmeldung fehlgeschlagen. Bitte erneut versuchen.',
            ]);
        }
    }

    private function redirectAfterLogin(User $user)
    {
        if ($user->isApproved() || $user->isAdmin()) {
            return redirect('/dashboard');
        }

        if ($user->isRejected()) {
            return redirect()->route('waitlist.rejected');
        }

        return redirect()->route('waitlist.pending');
    }
}
