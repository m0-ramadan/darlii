<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthController
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $loginInput = trim((string) ($r->input('login') ?: $r->input('email', '')));
        $password = (string) $r->input('password', '');
        $isAr = app()->getLocale() === 'ar';

        if ($loginInput === '' || $password === '') {
            $msg = $isAr ? 'يرجى إدخال البريد الإلكتروني أو رقم الهاتف وكلمة المرور.' : 'Please enter email or phone and password.';
            if ($r->wantsJson() || $r->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['login' => [$msg], 'email' => [$msg]],
                ], 422);
            }
            return back()->withErrors(['login' => $msg])->onlyInput('login', 'email');
        }

        $credentials = [];
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $credentials = ['email' => $loginInput, 'password' => $password];
        } else {
            // Normalize phone number (digits only search + raw match)
            $digits = preg_replace('/\D+/', '', $loginInput);
            $userByPhone = User::query()
                ->where('phone', $loginInput)
                ->when($digits !== '', function ($q) use ($digits) {
                    $q->orWhere('phone', $digits)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', '') = ?", [$digits]);
                })
                ->first();

            if ($userByPhone) {
                $credentials = ['id' => $userByPhone->id, 'password' => $password];
            }
        }

        if (empty($credentials) || ! Auth::attempt($credentials, $r->boolean('remember'))) {
            $msg = $isAr ? 'بيانات تسجيل الدخول (البريد/الهاتف أو كلمة المرور) غير صحيحة.' : __('auth.failed');
            if ($r->wantsJson() || $r->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['login' => [$msg], 'email' => [$msg]],
                ], 422);
            }

            return back()->withErrors(['login' => $msg])->onlyInput('login', 'email');
        }

        $user = Auth::user();

        // Check if user is suspended
        if ($user && $user->status === 'suspended') {
            Auth::logout();
            $r->session()->invalidate();
            $r->session()->regenerateToken();

            $suspendedMsg = $isAr ? 'تم إيقاف هذا الحساب من قبل الإدارة. يرجى التواصل مع إدارة المنصة.' : 'Your account has been suspended.';
            if ($r->wantsJson() || $r->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $suspendedMsg,
                    'errors' => ['login' => [$suspendedMsg]],
                ], 403);
            }
            return back()->withErrors(['login' => $suspendedMsg]);
        }

        $r->session()->regenerate();

        $locale = app()->getLocale();
        // Every website account has a private dashboard. CMS staff keep using /admin.
        $targetUrl = route('dashboard.index', ['locale' => $locale]);
        if ($user
            && $user->hasAnyRole(\App\Support\PermissionCatalog::panelRoles())
            && ! $user->hasRole('agency-manager')
            && ! $user->hasRole('agent')) {
            $targetUrl = url('/admin');
        }
        $targetUrl = redirect()->intended($targetUrl)->getTargetUrl();

        if ($r->wantsJson() || $r->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $isAr ? 'تم تسجيل الدخول بنجاح!' : 'Logged in successfully!',
                'redirect' => $targetUrl,
                'user' => [
                    'id' => $user?->id,
                    'name' => $user?->name,
                    'email' => $user?->email,
                    'status' => $user?->status,
                    'is_pending' => $user?->status === 'pending',
                ],
            ]);
        }

        return redirect()->intended($targetUrl);
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $r)
    {
        $locale = app()->getLocale();
        $isAr = $locale === 'ar';
        $accountType = $r->input('account_type', 'client');
        if (! in_array($accountType, ['client', 'company', 'broker'], true)) {
            $accountType = 'client';
        }

        if ($accountType === 'company') {
            // -------------------------------- COMPANY REGISTRATION --------------------------------
            $validated = $r->validate([
                'name' => ['required', 'string', 'max:120'],
                'phone' => ['required', 'string', 'max:50'],
                'company_name' => ['required', 'string', 'max:190'],
                'company_email' => ['required', 'email', 'max:190', 'unique:users,email'],
                'password' => ['required', 'confirmed', 'min:8'],
            ], [
                'name.required' => $isAr ? 'يرجى إدخال اسم المسؤول.' : 'Contact name is required.',
                'phone.required' => $isAr ? 'يرجى إدخال رقم الهاتف.' : 'Phone number is required.',
                'company_name.required' => $isAr ? 'يرجى إدخال اسم الشركة.' : 'Company name is required.',
                'company_email.required' => $isAr ? 'يرجى إدخال البريد الإلكتروني للشركة.' : 'Company email is required.',
                'company_email.unique' => $isAr ? 'البريد الإلكتروني للشركة مسجل لدينا بالفعل.' : 'Company email is already registered.',
                'password.required' => $isAr ? 'يرجى إدخال كلمة المرور.' : 'Password is required.',
                'password.confirmed' => $isAr ? 'كلمات المرور غير متطابقة.' : 'Passwords do not match.',
            ]);

            $u = User::create([
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['company_email'])),
                'password' => $validated['password'],
                'phone' => trim($validated['phone']),
                'job_title' => 'مسؤول شركة / وكالة عقارية',
                'status' => 'pending', // معلق حتى اعتماد الإدارة
            ]);

            if ($role = Role::where('slug', 'agency-manager')->first()) {
                $u->roles()->syncWithoutDetaching([$role->id]);
            }

            // Create Company Agency record
            $agency = \App\Models\Agency::create([
                'name' => trim($validated['company_name']),
                'slug' => \Illuminate\Support\Str::slug($validated['company_name']) . '-' . $u->id,
                'email' => strtolower(trim($validated['company_email'])),
                'phone' => trim($validated['phone']),
                'status' => 'pending',
            ]);
            $agency->users()->syncWithoutDetaching([$u->id => ['role' => 'manager']]);

            event(new Registered($u));
            Auth::login($u);

            $msg = $isAr
                ? 'تم تسجيل حساب الشركة بنجاح! حسابك حالياً قيد المراجعة والاعتماد من قبل إدارة المنصة.'
                : 'Company registered successfully! Your account is pending admin approval.';

            $redirectUrl = route('dashboard.index', ['locale' => $locale]);

            if ($r->wantsJson() || $r->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'redirect' => $redirectUrl,
                    'user' => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'status' => 'pending',
                        'is_pending' => true,
                    ],
                ]);
            }

            return redirect($redirectUrl)->with('status_warning', $msg);

        } elseif ($accountType === 'broker') {
            // -------------------------------- BROKER REGISTRATION --------------------------------
            $validated = $r->validate([
                'name' => ['required', 'string', 'max:120'],
                'phone' => ['required', 'string', 'max:50'],
                'national_id_image' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:10240'],
                'email' => ['nullable', 'email', 'max:190', 'unique:users,email'],
                'password' => ['required', 'confirmed', 'min:8'],
            ], [
                'name.required' => $isAr ? 'يرجى إدخال الاسم بالكامل كما في بطاقة الرقم القومي.' : 'Full name is required.',
                'phone.required' => $isAr ? 'يرجى إدخال رقم الهاتف.' : 'Phone number is required.',
                'national_id_image.required' => $isAr ? 'يرجى رفع صورة بطاقة الرقم القومي لاعتماد الحساب.' : 'National ID card image is required.',
                'password.required' => $isAr ? 'يرجى إدخال كلمة المرور.' : 'Password is required.',
                'password.confirmed' => $isAr ? 'كلمات المرور غير متطابقة.' : 'Passwords do not match.',
            ]);

            // Save National ID card image
            $idCardPath = null;
            if ($r->hasFile('national_id_image')) {
                $file = $r->file('national_id_image');
                $idCardPath = $file->store('agents/national_ids/' . now()->format('Y/m'), 'public');
            }

            // Fallback email for phone-based brokers
            $email = $validated['email'] ?? null;
            if (! $email) {
                $digits = preg_replace('/\D+/', '', $validated['phone']) ?: time();
                $candidate = "broker_{$digits}@darlii.eg";
                if (User::where('email', $candidate)->exists()) {
                    $candidate = "broker_{$digits}_" . \Illuminate\Support\Str::random(4) . "@darlii.eg";
                }
                $email = strtolower($candidate);
            }

            $u = User::create([
                'name' => trim($validated['name']),
                'email' => $email,
                'password' => $validated['password'],
                'phone' => trim($validated['phone']),
                'job_title' => 'وسيط عقاري / سمسار',
                'status' => 'pending', // معلق حتى اعتماد الإدارة
            ]);

            if ($role = Role::where('slug', 'agent')->first()) {
                $u->roles()->syncWithoutDetaching([$role->id]);
            }

            // Create Agent Profile with national ID image
            $profile = \App\Models\AgentProfile::create([
                'user_id' => $u->id,
                'slug' => \Illuminate\Support\Str::slug($validated['name']) . '-' . $u->id,
                'job_title' => 'وسيط عقاري',
                'status' => 'pending',
                'whatsapp' => trim($validated['phone']),
                'contract_terms' => $idCardPath,
            ]);
            $profile->setAgentTypeAttribute('broker');
            $profile->save();

            event(new Registered($u));
            Auth::login($u);

            $msg = $isAr
                ? 'تم تسجيل حساب السمسار بنجاح! حسابك حالياً قيد المراجعة والاعتماد من قبل إدارة المنصة.'
                : 'Broker registered successfully! Your account is pending admin approval.';

            $redirectUrl = route('dashboard.index', ['locale' => $locale]);

            if ($r->wantsJson() || $r->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'redirect' => $redirectUrl,
                    'user' => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'status' => 'pending',
                        'is_pending' => true,
                    ],
                ]);
            }

            return redirect($redirectUrl)->with('status_warning', $msg);

        } else {
            // -------------------------------- CLIENT / CUSTOMER REGISTRATION --------------------------------
            $validated = $r->validate([
                'name' => ['nullable', 'string', 'max:120'],
                'first_name' => ['nullable', 'string', 'max:120'],
                'last_name' => ['nullable', 'string', 'max:120'],
                'phone' => ['required', 'string', 'max:50'],
                'email' => ['nullable', 'email', 'max:190', 'unique:users,email'],
                'password' => ['required', 'confirmed', 'min:8'],
            ], [
                'phone.required' => $isAr ? 'يرجى إدخال رقم الهاتف.' : 'Phone number is required.',
                'password.required' => $isAr ? 'يرجى إدخال كلمة المرور.' : 'Password is required.',
                'password.confirmed' => $isAr ? 'كلمات المرور غير متطابقة.' : 'Passwords do not match.',
            ]);

            $name = trim((string) ($validated['name'] ?? trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''))));
            if ($name === '') {
                $name = $validated['phone'];
            }

            // Fallback email for phone-based clients
            $email = $validated['email'] ?? null;
            if (! $email) {
                $digits = preg_replace('/\D+/', '', $validated['phone']) ?: time();
                $candidate = "client_{$digits}@darlii.eg";
                if (User::where('email', $candidate)->exists()) {
                    $candidate = "client_{$digits}_" . \Illuminate\Support\Str::random(4) . "@darlii.eg";
                }
                $email = strtolower($candidate);
            }

            $u = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $validated['password'],
                'phone' => trim($validated['phone']),
                'status' => 'active', // العميل العادي نشط فوراً
            ]);

            if ($role = Role::where('slug', 'customer')->first()) {
                $u->roles()->syncWithoutDetaching([$role->id]);
            }

            event(new Registered($u));
            Auth::login($u);

            $msg = $isAr ? 'تم إنشاء الحساب بنجاح!' : 'Account registered successfully!';
            $redirectUrl = route('dashboard.index', ['locale' => $locale]);

            if ($r->wantsJson() || $r->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'redirect' => $redirectUrl,
                    'user' => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'status' => 'active',
                        'is_pending' => false,
                    ],
                ]);
            }

            return redirect($redirectUrl);
        }
    }
    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('home', ['locale' => app()->getLocale()]);
    }
    public function forgotForm()
    {
        return view('auth.forgot');
    }
    public function forgot(Request $r)
    {
        $r->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($r->only('email'));
        return $status === Password::RESET_LINK_SENT ? back()->with('status', __($status)) : back()->withErrors(['email' => __($status)]);
    }
    public function resetForm(Request $r, string $token)
    {
        return view('auth.reset', ['token' => $token, 'email' => $r->email]);
    }
    public function reset(Request $r)
    {
        $r->validate(['token' => 'required', 'email' => 'required|email', 'password' => 'required|confirmed|min:8']);
        $status = Password::reset($r->only('email', 'password', 'password_confirmation', 'token'), function (User $u, string $password) {
            $u->forceFill(['password' => $password])->save();
        });
        return $status === Password::PASSWORD_RESET ? redirect()->route('login', ['locale' => app()->getLocale()])->with('status', __($status)) : back()->withErrors(['email' => __($status)]);
    }
}
