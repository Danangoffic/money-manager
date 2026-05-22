<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CategoryService;
use App\Services\HouseholdService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function __construct(
        private HouseholdService $householdService,
        private CategoryService $categoryService,
    ) {}

    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            Auth::login($user);

            return redirect('/dashboard');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'google_token' => $googleUser->token,
                'profile_picture' => $googleUser->getAvatar(),
            ]);
            Auth::login($user);

            return redirect('/dashboard');
        }

        $newUser = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'google_token' => $googleUser->token,
            'profile_picture' => $googleUser->getAvatar(),
        ]);

        $household = $this->householdService->createWithOwner($newUser, $newUser->name."'s Household");
        $this->categoryService->seedDefaults($household->id);

        Auth::login($newUser);

        return redirect('/dashboard');
    }
}
