<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\SocialProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        try
        {
            $social_user = Socialite::driver($provider)->user();
        }
        catch (\Exception $exception)
        {
            return redirect('/');
        }

        // check if we have logged provider
        $social_provider = SocialProvider::where('provider_id', $social_user->getId())->first();
        if (!$social_provider)
        {
            // create a new user and provider
            $user = User::firstOrCreate(
                ['email' => $social_user->getEmail()],
                ['name' => $social_user->getName()]
            );

            $user->socialProviders()->create(
                ['provider_id' => $social_user->getId(), 'provider' => $provider]
            );

        } else
        {
            $user = $social_provider->user;
        }

        Auth::login($user);

        return redirect($this->redirectTo);

    }





}
