<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\BhwProfile;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return response()->view('login')->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // "Email or Phone Number": search the right column
        $isEmail = filter_var($data['login'], FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'phone';
        $value = $isEmail ? $data['login'] : preg_replace('/[\s\-]/', '', $data['login']);

        if (! Auth::attempt([$field => $value, 'password' => $data['password']])) {
            throw ValidationException::withMessages(['login' => 'Wrong email/phone number or password.']);
        }

        $user = Auth::user();

        if ($user->status !== 'approved') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'login' => $user->status === 'pending'
                    ? 'Your account is waiting for approval.'
                    : 'Your sign-up was not approved. Please contact your barangay.',
            ]);
        }

        $request->session()->regenerate();

        return redirect($user->role === 'bhw' ? '/bhw/dashboard' : '/resident/home');
    }

    public function showStep1()
    {
        return response()
            ->view('register', ['barangays' => Barangay::orderBy('name')->get()])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function storeStep1(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'barangay_id' => 'required|exists:barangays,id',
            'role' => ['required', Rule::in(['resident', 'bhw'])],
        ]);

        $request->session()->put('register', $data);

        return redirect('/register/step-2');
    }

    public function showStep2(Request $request)
    {
            if (! $request->session()->has('register')) {
            return redirect('/register');
        }

        return response()->view('register-step2')->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function storeStep2(Request $request)
    {
        $step1 = $request->session()->get('register');
        if (! $step1) {
            return redirect('/register');
        }

        $request->merge(['phone' => preg_replace('/[\s\-]/', '', (string) $request->input('phone'))]);

        $data = $request->validate([
            'phone' => ['required', 'regex:/^(09\d{9}|\+639\d{9})$/', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'phone.regex' => 'Enter a valid mobile number, like 09171234567.',
            'phone.unique' => 'That phone number is already registered.',
            'email.unique' => 'That email is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'The two passwords do not match.',
        ]);

        DB::transaction(function () use ($step1, $data) {
            $user = User::create([
                'name' => $step1['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => $data['password'],   // hashed by the model
                'role' => $step1['role'],
                'status' => 'pending',
            ]);

            if ($step1['role'] === 'resident') {
                Resident::create([
                    'user_id' => $user->id,
                    'barangay_id' => $step1['barangay_id'],
                    'full_name' => $step1['full_name'],
                    'phone' => $user->phone,
                    'email' => $user->email,
                ]);
            } else {
                BhwProfile::create([
                    'user_id' => $user->id,
                    'barangay_id' => $step1['barangay_id'],
                ]);
            }
        });

        $request->session()->forget('register');

        return redirect('/login')->with('status', 'Account created. Please wait for approval before logging in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}