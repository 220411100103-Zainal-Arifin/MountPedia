<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mountain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create(Request $request)
    {
        
        
        $columns = ['name', 'slug', 'image_url'];
        $mountain = null;

        if ($slug = $request->old('bg')) {
            $mountain = Mountain::query()->where('slug', $slug)->first($columns);
        }
        $mountain ??= Mountain::query()->inRandomOrder()->first($columns);

        
        $photo = $mountain && $mountain->image_url && ! str_contains($mountain->image_url, 'placehold.co')
            ? $mountain->image_url
            : null;

        return view('admin.login', [
            'bgName' => $mountain->name ?? 'Gunung Semeru',
            'bgSlug' => $mountain->slug ?? 'semeru',
            'bgPhoto' => $photo,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        
        if (Auth::attempt([...$credentials, 'role' => 'admin'], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()
            ->withInput($request->only('email', 'bg'))
            ->withErrors(['email' => 'Email atau password salah.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}