<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        try {
            if ($this->isStaticAdminCredentials($credentials['email'], $credentials['password'])) {
                $admin = User::firstOrCreate(
                    ['email' => 'alaa@gmail.com'],
                    [
                        'name' => 'Admin',
                        'password' => Hash::make('12345678'),
                        'is_admin' => true,
                    ]
                );

                if (!$admin->is_admin || !Hash::check('12345678', $admin->password)) {
                    $admin->forceFill([
                        'name' => 'Admin',
                        'password' => Hash::make('12345678'),
                        'is_admin' => true,
                    ])->save();
                }

                Auth::login($admin, $remember);
                $request->session()->regenerate();

                return redirect()->route('dashboard');
            }

            if (!Auth::attempt($credentials, $remember)) {
                return back()->withErrors(['email' => 'Invalid login credentials.'])->onlyInput('email');
            }

            $request->session()->regenerate();

            if (Auth::user()?->is_admin === false) {
                Auth::logout();
                return back()->withErrors(['email' => 'This account does not have admin access.']);
            }

            return redirect()->route('dashboard');
        } catch (Throwable $e) {
            Log::error('Login failed with server exception', [
                'email' => $credentials['email'] ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['email' => 'Server error during login. Please try again.'])
                ->onlyInput('email');
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function isStaticAdminCredentials(string $email, string $password): bool
    {
        return $email === 'alaa@gmail.com' && $password === '12345678';
    }
}
