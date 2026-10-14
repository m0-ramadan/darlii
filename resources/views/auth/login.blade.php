@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

@section('title', $isAr ? 'تسجيل الدخول' : 'Sign In')

@section('content')
<div class="container page-section" style="padding-top: 50px; padding-bottom: 70px;">
    <div class="mx-auto card shadow-sm border-0 p-4 p-md-5" style="max-width: 480px; border-radius: 16px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold mb-1">{{ $isAr ? 'تسجيل الدخول' : 'Sign In' }}</h2>
            <p class="text-muted" style="font-size: 14px;">{{ $isAr ? 'أهلاً بك مجدداً في منصة دارلي العقارية' : 'Welcome back to Darli Real Estate Platform' }}</p>
        </div>

        @if (session('status_warning'))
            <div class="alert alert-warning mb-4" style="border-radius: 10px; font-size: 13.5px;">
                {{ session('status_warning') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login', ['locale' => $locale]) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size: 14px;">{{ $isAr ? 'البريد الإلكتروني أو رقم الهاتف' : 'Email or Phone' }}</label>
                <input name="login" type="text" class="form-control" style="border-radius: 10px; padding: 11px 14px;" value="{{ old('login', old('email')) }}" placeholder="{{ $isAr ? '010xxxxxxxx أو name@example.com' : '010xxxxxxxx or name@example.com' }}" required autofocus>
                @error('login')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size: 14px;">{{ $isAr ? 'كلمة المرور' : 'Password' }}</label>
                <input name="password" type="password" class="form-control" style="border-radius: 10px; padding: 11px 14px;" placeholder="••••••••" required>
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4" style="font-size: 13.5px;">
                <label class="form-check-label d-flex align-items-center gap-2 mb-0" style="cursor: pointer;">
                    <input class="form-check-input mt-0" type="checkbox" name="remember" value="1" checked>
                    <span>{{ $isAr ? 'تذكرني' : 'Remember me' }}</span>
                </label>
                <a href="{{ route('password.request', ['locale' => $locale]) }}" class="text-muted text-decoration-none">
                    {{ $isAr ? 'نسيت كلمة المرور؟' : 'Forgot password?' }}
                </a>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="background: #db1d23; border-color: #db1d23; border-radius: 10px;">
                {{ $isAr ? 'تسجيل الدخول' : 'Sign In' }}
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top" style="font-size: 14px;">
            <span class="text-muted">{{ $isAr ? 'ليس لديك حساب بعد؟' : "Don't have an account?" }}</span>
            <a href="{{ route('register', ['locale' => $locale]) }}" class="fw-bold ms-1" style="color: #db1d23;">
                {{ $isAr ? 'إنشاء حساب جديد' : 'Register now' }}
            </a>
        </div>
    </div>
</div>
@endsection
