<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Determine waitlist status atomically: first 25 registrations get free tier + immediate approval.
        $user = DB::transaction(function () use ($request) {
            // Lock the approved-count row to prevent race conditions
            $approvedCount = User::where('waitlist_status', 'approved')
                ->lockForUpdate()
                ->count();

            $isFree        = $approvedCount < User::FREE_TIER_LIMIT;
            $status        = $isFree ? 'approved' : 'waiting';
            $approvedAt    = $isFree ? now() : null;

            return User::create([
                'name'             => $request->name,
                'email'            => $request->email,
                'password'         => Hash::make($request->password),
                'scans_limit'      => 100,
                'waitlist_status'  => $status,
                'is_free_tier'     => $isFree,
                'approved_at'      => $approvedAt,
            ]);
        });

        event(new Registered($user));
        Auth::login($user);
        Mail::to($user->email)->queue(new WelcomeMail($user));

        // Redirect approved users straight to dashboard; waitlisted users see pending page
        if ($user->isApproved()) {
            return redirect(route('dashboard', absolute: false));
        }

        return redirect()->route('waitlist.pending');
    }
}
