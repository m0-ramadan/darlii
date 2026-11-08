@extends('layouts.public-shell')

@section('title', 'تعديل طلب عميل')

@push('head')
    <link rel="stylesheet" href="{{ asset('themes/darlii/css/dashboard-requests.css') }}?v=1.0.0">
@endpush

@section('content')
    <div class="darli-dash-wrapper cr-page">
        <div class="darli-dash-hero"><div class="container">
            <div class="darli-dash-breadcrumb"><a href="{{ route('dashboard.index') }}">لوحة التحكم</a><span class="sep">›</span><a href="{{ route('dashboard.customer-requests.index') }}">طلبات العملاء</a><span class="sep">›</span><span>تعديل</span></div>
            <div class="cr-head"><div><h1 class="cr-title">تعديل طلب: {{ $customerRequest->customer_name }}</h1><p class="cr-sub">{{ $customerRequest->phone }}</p></div><a href="{{ route('dashboard.customer-requests.index') }}" class="cr-back">← رجوع للطلبات</a></div>
        </div></div>
        <div class="container"><div class="cr-shell">@include('dashboard.customer-requests._sidebar')<div class="cr-main">@include('dashboard.customer-requests._form')</div></div></div>
    </div>
@endsection
