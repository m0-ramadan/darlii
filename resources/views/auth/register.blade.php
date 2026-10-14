@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

@section('title', $isAr ? 'إنشاء حساب جديد' : 'Register Account')

@section('content')
<div class="container page-section" style="padding-top: 40px; padding-bottom: 70px;">
    <div class="mx-auto card shadow-sm border-0 p-4 p-md-5" style="max-width: 560px; border-radius: 16px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold mb-1">{{ $isAr ? 'إنشاء حساب جديد في دارلي' : 'Create an Account on Darli' }}</h2>
            <p class="text-muted" style="font-size: 14px;">{{ $isAr ? 'اختر نوع الحساب وأدخل بياناتك للانضمام إلى منصة دارلي' : 'Select account type and enter your details' }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4" style="border-radius: 10px; font-size: 13.5px;">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register', ['locale' => $locale]) }}" enctype="multipart/form-data" id="standalone-register-form">
            @csrf
            <input type="hidden" name="account_type" id="page-reg-account-type" value="{{ old('account_type', 'client') }}">

            <!-- Role Selector -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size: 14px;">{{ $isAr ? 'أنت تسجل بصفتك:' : 'You are registering as:' }}</label>
                <div class="d-flex gap-2">
                    <div class="flex-fill text-center p-2 rounded-3 border role-option @if(old('account_type', 'client') === 'client') active border-danger bg-danger-subtle @endif" data-role="client" style="cursor: pointer;">
                        <span class="fw-bold d-block" style="font-size: 0.95rem;">{{ $isAr ? 'عميل' : 'Customer' }}</span>
                        <span class="text-muted" style="font-size: 0.75rem;">{{ $isAr ? 'باحث عن عقار' : 'Property seeker' }}</span>
                    </div>
                    <div class="flex-fill text-center p-2 rounded-3 border role-option @if(old('account_type') === 'company') active border-danger bg-danger-subtle @endif" data-role="company" style="cursor: pointer;">
                        <span class="fw-bold d-block" style="font-size: 0.95rem;">{{ $isAr ? 'شركة' : 'Company' }}</span>
                        <span class="text-muted" style="font-size: 0.75rem;">{{ $isAr ? 'تطوير وتسويق' : 'Real estate' }}</span>
                    </div>
                    <div class="flex-fill text-center p-2 rounded-3 border role-option @if(old('account_type') === 'broker') active border-danger bg-danger-subtle @endif" data-role="broker" style="cursor: pointer;">
                        <span class="fw-bold d-block" style="font-size: 0.95rem;">{{ $isAr ? 'سمسار' : 'Broker' }}</span>
                        <span class="text-muted" style="font-size: 0.75rem;">{{ $isAr ? 'وسيط عقاري حر' : 'Agent' }}</span>
                    </div>
                </div>
            </div>

            <!-- Role Notice -->
            <div id="page-role-notice" class="p-2 px-3 mb-3 @if(old('account_type', 'client') === 'client') d-none @endif" style="background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; font-size: 0.82rem; color: #92400E;">
                ⏳ <strong>{{ $isAr ? 'ملاحظة:' : 'Notice:' }}</strong>
                <span id="page-role-notice-text">
                    {{ $isAr ? 'حسابات الشركات والوسطاء تخضع للمراجعة والاعتماد من قبل إدارة دارلي قبل إتاحة إضافة ونشر العقارات والمشاريع.' : 'Partner accounts require review and activation by Darli admin.' }}
                </span>
            </div>

            <!-- Name Field -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size: 14px;">
                    <span id="page-label-name">{{ old('account_type') === 'company' ? ($isAr ? 'اسم مسؤول التواصل' : 'Contact Person') : ($isAr ? 'الاسم بالكامل' : 'Full Name') }}</span> <span class="text-danger">*</span>
                </label>
                <input name="name" type="text" class="form-control" style="border-radius: 10px; padding: 11px 14px;" value="{{ old('name') }}" placeholder="{{ $isAr ? 'محمد أحمد علي' : 'Full Name' }}" required>
            </div>

            <!-- Phone Field -->
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size: 14px;">
                    <span id="page-label-phone">{{ $isAr ? 'رقم الهاتف' : 'Phone Number' }}</span> <span class="text-danger">*</span>
                </label>
                <input name="phone" type="tel" class="form-control" style="border-radius: 10px; padding: 11px 14px;" value="{{ old('phone') }}" placeholder="01012345678" required dir="ltr">
            </div>

            <!-- Company Fields -->
            <div id="page-company-fields" class="@if(old('account_type') !== 'company') d-none @endif">
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 14px;">{{ $isAr ? 'اسم الشركة / الوكالة العقارية' : 'Company Name' }} <span class="text-danger">*</span></label>
                    <input name="company_name" id="page-company-name" type="text" class="form-control" style="border-radius: 10px; padding: 11px 14px;" value="{{ old('company_name') }}" placeholder="{{ $isAr ? 'شركة النيل للتطوير العقاري' : 'Al-Nile Real Estate' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 14px;">{{ $isAr ? 'البريد الإلكتروني الرسمي للشركة' : 'Official Company Email' }} <span class="text-danger">*</span></label>
                    <input name="company_email" id="page-company-email" type="email" class="form-control" style="border-radius: 10px; padding: 11px 14px;" value="{{ old('company_email') }}" placeholder="info@company.com" dir="ltr">
                </div>
            </div>

            <!-- Broker Fields (National ID Upload) -->
            <div id="page-broker-fields" class="@if(old('account_type') !== 'broker') d-none @endif">
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 14px;">{{ $isAr ? 'صورة بطاقة الرقم القومي' : 'National ID Card' }} <span class="text-danger">*</span></label>
                    <input name="national_id_image" id="page-national-id" type="file" class="form-control" style="border-radius: 10px;" accept="image/*,.pdf">
                    <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">{{ $isAr ? 'يرجى إرفاق صورة واضحة لبطاقة الرقم القومي لاعتماد وتفعيل حسابك.' : 'Upload your National ID for verification.' }}</small>
                </div>
            </div>

            <!-- Password Fields -->
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 14px;">{{ $isAr ? 'كلمة المرور' : 'Password' }} <span class="text-danger">*</span></label>
                    <input name="password" type="password" class="form-control" style="border-radius: 10px; padding: 11px 14px;" placeholder="••••••••" required minlength="8">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size: 14px;">{{ $isAr ? 'تأكيد كلمة المرور' : 'Confirm Password' }} <span class="text-danger">*</span></label>
                    <input name="password_confirmation" type="password" class="form-control" style="border-radius: 10px; padding: 11px 14px;" placeholder="••••••••" required minlength="8">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mt-2" style="background: #db1d23; border-color: #db1d23; border-radius: 10px;" id="page-submit-btn">
                {{ $isAr ? 'إنشاء الحساب' : 'Create Account' }}
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top" style="font-size: 14px;">
            <span class="text-muted">{{ $isAr ? 'لديك حساب بالفعل؟' : 'Already have an account?' }}</span>
            <a href="{{ route('login', ['locale' => $locale]) }}" class="fw-bold ms-1" style="color: #db1d23;">
                {{ $isAr ? 'تسجيل الدخول' : 'Sign in' }}
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var roleOptions = document.querySelectorAll('.role-option');
    var accountTypeInput = document.getElementById('page-reg-account-type');
    var companyFields = document.getElementById('page-company-fields');
    var brokerFields = document.getElementById('page-broker-fields');
    var roleNotice = document.getElementById('page-role-notice');
    var roleNoticeText = document.getElementById('page-role-notice-text');
    var labelName = document.getElementById('page-label-name');
    var submitBtn = document.getElementById('page-submit-btn');
    var isAr = '{{ $isAr ? "1" : "0" }}' === '1';

    var companyNameInput = document.getElementById('page-company-name');
    var companyEmailInput = document.getElementById('page-company-email');
    var nationalIdInput = document.getElementById('page-national-id');

    function setRole(role) {
        accountTypeInput.value = role;
        roleOptions.forEach(function (opt) {
            if (opt.getAttribute('data-role') === role) {
                opt.classList.add('active', 'border-danger', 'bg-danger-subtle');
            } else {
                opt.classList.remove('active', 'border-danger', 'bg-danger-subtle');
            }
        });

        if (role === 'company') {
            companyFields.classList.remove('d-none');
            brokerFields.classList.add('d-none');
            roleNotice.classList.remove('d-none');
            roleNoticeText.textContent = isAr
                ? 'حسابات الشركات تخضع للمراجعة والاعتماد السريع من إدارة المنصة قبل تفعيل إضافة العقارات والمشاريع.'
                : 'Company accounts require review by Darli admin.';
            labelName.textContent = isAr ? 'اسم مسؤول التواصل' : 'Contact Person';
            submitBtn.textContent = isAr ? 'تسجيل حساب شركة جديدة' : 'Register Company Account';
            if (companyNameInput) companyNameInput.required = true;
            if (companyEmailInput) companyEmailInput.required = true;
            if (nationalIdInput) nationalIdInput.required = false;
        } else if (role === 'broker') {
            companyFields.classList.add('d-none');
            brokerFields.classList.remove('d-none');
            roleNotice.classList.remove('d-none');
            roleNoticeText.textContent = isAr
                ? 'حسابات السماسرة تخضع لمراجعة بطاقة الرقم القومي واعتماد إدارة دارلي قبل إتاحة نشر العقارات.'
                : 'Broker accounts require National ID verification.';
            labelName.textContent = isAr ? 'الاسم بالكامل كما في بطاقة الرقم القومي' : 'Full Name (National ID)';
            submitBtn.textContent = isAr ? 'تسجيل حساب سمسار' : 'Register Broker Account';
            if (companyNameInput) companyNameInput.required = false;
            if (companyEmailInput) companyEmailInput.required = false;
            if (nationalIdInput) nationalIdInput.required = true;
        } else {
            companyFields.classList.add('d-none');
            brokerFields.classList.add('d-none');
            roleNotice.classList.add('d-none');
            labelName.textContent = isAr ? 'الاسم بالكامل' : 'Full Name';
            submitBtn.textContent = isAr ? 'إنشاء حساب عميل جديد' : 'Create Customer Account';
            if (companyNameInput) companyNameInput.required = false;
            if (companyEmailInput) companyEmailInput.required = false;
            if (nationalIdInput) nationalIdInput.required = false;
        }
    }

    roleOptions.forEach(function (opt) {
        opt.addEventListener('click', function () {
            setRole(this.getAttribute('data-role'));
        });
    });
});
</script>
@endsection
