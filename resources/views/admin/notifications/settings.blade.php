@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="{{ admin_route('admin.notifications.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 15])
            سجل الإشعارات
        </a>
        <a href="{{ admin_route('admin.notifications.create') }}" class="ap-btn ap-btn--primary ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'send', 'size' => 15])
            إرسال إشعار
        </a>
    </div>
@endsection

@section('content')
    {{-- Tabs --}}
    <div class="ap-tabs mb-4">
        <a href="{{ admin_route('admin.notifications.index') }}" class="ap-tab">
            @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 16])
            سجل الإشعارات المرسلة
        </a>
        <a href="{{ admin_route('admin.notifications.create') }}" class="ap-tab">
            @include('admin.partials.icon', ['iconName' => 'send', 'size' => 16])
            إرسال إشعار جديد
        </a>
        <a href="{{ admin_route('admin.notifications.settings') }}" class="ap-tab is-active">
            @include('admin.partials.icon', ['iconName' => 'settings', 'size' => 16])
            إعدادات Firebase و Web Push
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ admin_route('admin.notifications.settings.update') }}">
                @csrf
                @method('PUT')

                {{-- Status & Service Activation --}}
                <div class="ap-card mb-4">
                    <div class="ap-card__header d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="ap-card__title">حالة نظام الإشعارات</h2>
                            <p class="ap-card__subtitle mb-0">تفعيل أو تعطيل استقبال وإرسال إشعارات الدفع الفورية (Web Push)</p>
                        </div>
                        <div class="form-check form-switch fs-4 m-0">
                            <input class="form-check-input" type="checkbox" role="switch" name="firebase_enabled" value="1" id="firebase_enabled" {{ setting('firebase.enabled', '1') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="ap-card__body">
                        <div class="alert {{ setting('firebase.enabled', '1') == '1' ? 'alert-success' : 'alert-warning' }} border-0 d-flex align-items-center gap-3 mb-0">
                            @include('admin.partials.icon', ['iconName' => setting('firebase.enabled', '1') == '1' ? 'check' : 'alert', 'size' => 22])
                            <div>
                                <strong>{{ setting('firebase.enabled', '1') == '1' ? 'نظام الإشعارات مفعل ويعمل حالياً.' : 'نظام الإشعارات معطل مؤقتاً.' }}</strong>
                                <div class="small opacity-75">عند التعطيل، لن تظهر مطالبات الاشتراك للمستخدمين ولن يتم بث رسائل الدفع عبر المتصفح.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Firebase App Credentials --}}
                <div class="ap-card mb-4">
                    <div class="ap-card__header">
                        <h2 class="ap-card__title">بيانات تطبيق الويب (Firebase Web App Config)</h2>
                        <p class="ap-card__subtitle mb-0">المعلومات المستخرجة من إعدادات مشروع Firebase (Project Settings > General > Web Apps)</p>
                    </div>
                    <div class="ap-card__body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark" for="firebase_api_key">Web API Key</label>
                                <input type="text" name="firebase_api_key" id="firebase_api_key" class="form-control font-monospace" value="{{ setting('firebase.api_key', 'AIzaSyBNv6O81fJnehjTBw12cc9IZOT-32Bfy9Q') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark" for="firebase_project_id">Project ID</label>
                                <input type="text" name="firebase_project_id" id="firebase_project_id" class="form-control font-monospace" value="{{ setting('firebase.project_id', 'khademate2') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark" for="firebase_auth_domain">Auth Domain</label>
                                <input type="text" name="firebase_auth_domain" id="firebase_auth_domain" class="form-control font-monospace" value="{{ setting('firebase.auth_domain', 'khademate2.firebaseapp.com') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark" for="firebase_storage_bucket">Storage Bucket</label>
                                <input type="text" name="firebase_storage_bucket" id="firebase_storage_bucket" class="form-control font-monospace" value="{{ setting('firebase.storage_bucket', 'khademate2.firebasestorage.app') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark" for="firebase_messaging_sender_id">Messaging Sender ID</label>
                                <input type="text" name="firebase_messaging_sender_id" id="firebase_messaging_sender_id" class="form-control font-monospace" value="{{ setting('firebase.messaging_sender_id', '504226887841') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark" for="firebase_app_id">App ID</label>
                                <input type="text" name="firebase_app_id" id="firebase_app_id" class="form-control font-monospace" value="{{ setting('firebase.app_id', '1:504226887841:web:cbc5abdc30304c6014d8e0') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark" for="firebase_measurement_id">Measurement ID (Google Analytics)</label>
                                <input type="text" name="firebase_measurement_id" id="firebase_measurement_id" class="form-control font-monospace" value="{{ setting('firebase.measurement_id', 'G-01HJBBQZP7') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FCM Server Key & VAPID Key --}}
                <div class="ap-card mb-4 border-primary border-opacity-25">
                    <div class="ap-card__header bg-primary bg-opacity-10">
                        <div class="d-flex align-items-center gap-2">
                            @include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18, 'class' => 'text-primary'])
                            <div>
                                <h2 class="ap-card__title text-primary mb-0">مفاتيح الدفع والخادم (Cloud Messaging Keys)</h2>
                                <p class="ap-card__subtitle mb-0">مطلوبة للبث التلقائي لإشعارات المتصفح والهاتف مباشرة من الخادم</p>
                            </div>
                        </div>
                    </div>
                    <div class="ap-card__body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark" for="firebase_server_key">Cloud Messaging Server Key (مفتاح الخادم)</label>
                            <input type="password" name="firebase_server_key" id="firebase_server_key" class="form-control font-monospace"
                                placeholder="AAAA... أو مفتاح الخادم القديم من Firebase Cloud Messaging" value="{{ setting('firebase.server_key', '') }}">
                            <div class="form-text">
                                يُستخدم هذا المفتاح من قِبل السيرفر لإرسال الإشعار لجميع الأجهزة المسجلة عبر FCM HTTP API.
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold small text-dark" for="firebase_vapid_public_key">Web Push VAPID Public Key (مفتاح الويب العام)</label>
                            <input type="text" name="firebase_vapid_public_key" id="firebase_vapid_public_key" class="form-control font-monospace"
                                placeholder="B... زوج مفاتيح الويب من تبويب Web Push Certificates" value="{{ setting('firebase.vapid_public_key', '') }}">
                            <div class="form-text">
                                يُستخدم لطلب إذن المتصفح وتوليد رمز الجهاز الفريد للمستخدم.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2">
                    <button type="submit" class="ap-btn ap-btn--primary px-4 py-2">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                        حفظ جميع الإعدادات
                    </button>
                </div>
            </form>
        </div>

        {{-- Help and Documentation Guide --}}
        <div class="col-lg-4">
            <div class="ap-card mb-4">
                <div class="ap-card__header">
                    <h3 class="ap-card__title">دليل ربط مفاتيح Firebase</h3>
                </div>
                <div class="ap-card__body" style="font-size: 13px; line-height: 1.6;">
                    <div class="mb-3 pb-3 border-bottom">
                        <h4 class="fw-bold text-dark fs-6 mb-1">1. أين أجد مفتاح الخادم (Server Key)؟</h4>
                        <ol class="ps-3 mb-0 text-muted">
                            <li>افتح <a href="https://console.firebase.google.com/" target="_blank" class="text-primary fw-medium">لوحة تحكم Firebase</a> وافتح مشروع <strong>khademate2</strong>.</li>
                            <li>انقر على أيقونة الترس ⚙️ بجانب Project Overview ثم اختر <strong>Project Settings</strong>.</li>
                            <li>انتقل إلى تبويب <strong>Cloud Messaging</strong>.</li>
                            <li>ستجد <strong>Server key</strong> أو <strong>Cloud Messaging API (Legacy)</strong>. إذا كان معطلاً، انقر على القائمة الثلاثية وقم بتفعيله في Google Cloud Console لنسخ المفتاح ولصقه هنا.</li>
                        </ol>
                    </div>

                    <div>
                        <h4 class="fw-bold text-dark fs-6 mb-1">2. أين أجد مفتاح VAPID Key؟</h4>
                        <ol class="ps-3 mb-0 text-muted">
                            <li>في نفس تبويب <strong>Cloud Messaging</strong>، انزل إلى قسم <strong>Web configuration</strong>.</li>
                            <li>تحت <strong>Web Push certificates</strong>، انقر على زر <strong>Generate key pair</strong> إذا لم يكن موجوداً.</li>
                            <li>انسخ المفتاح الظاهر والصقه في حقل VAPID Public Key أعلاه.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="ap-card border-success border-opacity-25 bg-success bg-opacity-10">
                <div class="ap-card__body p-3">
                    <div class="d-flex gap-2">
                        <div class="text-success">
                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])
                        </div>
                        <div class="small">
                            <strong class="d-block text-dark mb-1">الإشعارات الداخلية تعمل دائماً!</strong>
                            <p class="mb-0 text-muted">حتى في حال عدم إدخال مفتاح الخادم، سيتم حفظ الإشعارات فوراً في قاعدة البيانات وعرضها للمستخدمين داخل الموقع في أيقونة الجرس والشريط العلوي مع الصور كاملة.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

