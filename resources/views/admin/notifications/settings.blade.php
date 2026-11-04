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
        <a href="{{ admin_route('admin.notifications.devices') }}" class="ap-tab">
            @include('admin.partials.icon', ['iconName' => 'users', 'size' => 16])
            المشتركون ورموز الأجهزة
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

    @php
        $cred = app(\App\Services\Notifications\FcmNotificationService::class)->resolveFirebaseCredentials();
        $hasPushCredentials = $cred['type'] !== 'none';
    @endphp

    @if(!$hasPushCredentials)
        <div class="alert alert-warning border-0 d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="text-warning">
                    @include('admin.partials.icon', ['iconName' => 'alert', 'size' => 28])
                </div>
                <div>
                    <strong class="d-block text-dark fs-6 mb-1">تنبيه: مفتاح بث الإشعارات (Server Key أو Service Account) غير مُدخل!</strong>
                    <div class="text-muted small">
                        حالياً تُحفظ الإشعارات داخل صندوق الموقع فقط وتظهر في جرس الصفحة، <strong>ولكن لا تصل إلى ستارة هواتف المستخدمين كإشعار فوري (Web Push) ولا تصدر صوتاً</strong> حتى تقوم بإدخال مفتاح الخادم أدناه.
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-success border-0 d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="text-success">
                    @include('admin.partials.icon', ['iconName' => 'check', 'size' => 28])
                </div>
                <div>
                    <strong class="d-block text-dark fs-6 mb-1">
                        نظام البث الفوري إلى ستارة الأجهزة متصل ويعمل بنجاح!
                        ({{ $cred['type'] === 'service_account' ? 'عبر Google Service Account v1' : 'عبر Cloud Messaging Server Key' }})
                    </strong>
                    <div class="text-muted small">
                        كافة الإشعارات الجديدة وتحديثات الأسعار ستبث فوراً إلى شاشات وستارة هواتف المشتركين مع نغمة التنبيه والاهتزاز.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ admin_route('admin.notifications.settings.update') }}" enctype="multipart/form-data">
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

                {{-- Cloud Messaging Server Key & Service Account --}}
                <div class="ap-card mb-4 border-primary border-opacity-25">
                    <div class="ap-card__header bg-primary bg-opacity-10 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            @include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18, 'class' => 'text-primary'])
                            <div>
                                <h2 class="ap-card__title text-primary mb-0">مفاتيح الدفع والخادم (Cloud Messaging Keys)</h2>
                                <p class="ap-card__subtitle mb-0">المفتاح المسؤول عن إرسال الإشعار لستارة الهاتف وإصدار صوت التنبيه</p>
                            </div>
                        </div>
                        @if($hasPushCredentials)
                            <span class="badge bg-success">متصل وجاهز</span>
                        @else
                            <span class="badge bg-danger">مفقود - الإشعار الداخلي فقط يعمل</span>
                        @endif
                    </div>
                    <div class="ap-card__body">
                        {{-- Method 1: Server Key --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between" for="firebase_server_key">
                                <span>1. مفتاح الخادم (Cloud Messaging Server Key)</span>
                                <span class="badge bg-light text-muted border">FCM Legacy API</span>
                            </label>
                            <input type="password" name="firebase_server_key" id="firebase_server_key" class="form-control font-monospace"
                                placeholder="AAAA... يبدأ عادة بـ AAAA من تبويب Cloud Messaging في لوحة تحكم Firebase" value="{{ setting('firebase.server_key', '') }}">
                            <div class="form-text">
                                يُنسخ من إعدادات مشروع Firebase &gt; Cloud Messaging &gt; Server Key.
                            </div>
                        </div>

                        <hr class="my-3 opacity-25">

                        {{-- Method 2: Service Account JSON (v1) --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between">
                                <span>2. أو حساب الخدمة المعتمد (Firebase Service Account JSON)</span>
                                <span class="badge bg-light text-primary border">FCM HTTP v1 (الأحدث)</span>
                            </label>
                            
                            <div class="mb-2">
                                <label class="form-label text-muted small" for="firebase_service_account_file">رفع ملف JSON مباشرة:</label>
                                <input type="file" name="firebase_service_account_file" id="firebase_service_account_file" class="form-control" accept=".json,application/json">
                                <div class="form-text">
                                    يمكنك تحميل ملف الاعتماد من Firebase Console: Project Settings &gt; Service accounts &gt; Generate new private key ورفعه هنا.
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="form-label text-muted small" for="firebase_service_account_json">أو لصق محتوى ملف JSON هنا:</label>
                                <textarea name="firebase_service_account_json" id="firebase_service_account_json" rows="3" class="form-control font-monospace" placeholder='{"type": "service_account", "project_id": "khademate2", ...}'>{{ setting('firebase.service_account_json', '') }}</textarea>
                            </div>
                        </div>

                        <hr class="my-3 opacity-25">

                        {{-- VAPID Public Key --}}
                        <div class="mb-2">
                            <label class="form-label fw-bold small text-dark" for="firebase_vapid_public_key">Web Push VAPID Public Key (مفتاح الويب العام)</label>
                            <input type="text" name="firebase_vapid_public_key" id="firebase_vapid_public_key" class="form-control font-monospace"
                                placeholder="B... زوج مفاتيح الويب من تبويب Web Push Certificates" value="{{ setting('firebase.vapid_public_key', '') }}">
                            <div class="form-text">
                                يُستخدم لطلب إذن المتصفح وتوليد رمز الجهاز الفريد للمستخدم (اختياري إذا تم استخدام الإعداد الافتراضي).
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
            {{-- Test Push Card --}}
            <div class="ap-card mb-4 border-info border-opacity-25 bg-info bg-opacity-10">
                <div class="ap-card__body p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="text-info">
                            @include('admin.partials.icon', ['iconName' => 'send', 'size' => 20])
                        </div>
                        <h4 class="fw-bold text-dark fs-6 mb-0">اختبار الإرسال لستارة الأجهزة</h4>
                    </div>
                    <p class="small text-muted mb-3">
                        أرسل إشعارًا تجريبيًا لتتأكد من ظهوره في ستارة جهازك مع الصوت والاهتزاز. يمكنك تخصيص جهاز واحد بالتوكن أو تركه فارغًا لإرسال إشعار لجميع الأجهزة المسجلة:
                    </p>
                    <form method="POST" action="{{ admin_route('admin.notifications.test-push') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-dark" for="test-push-token">توكن الجهاز (اختياري)</label>
                            <input id="test-push-token" type="text" name="token" class="form-control form-control-sm" dir="ltr"
                                placeholder="cDOJ...:APA91b..." autocomplete="off">
                            <div class="form-text" style="font-size: 11.5px;">
                                الصق توكن جهازك لإرسال إشعار تجريبي له فقط — أو اتركه فارغًا لإرساله لكل الأجهزة المسجلة.
                            </div>
                        </div>
                        <button type="submit" class="ap-btn ap-btn--primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" {{ !$hasPushCredentials ? 'disabled' : '' }}>
                            @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 16])
                            إرسال إشعار تجريبي الآن
                        </button>
                    </form>
                    @if(!$hasPushCredentials)
                        <div class="text-danger small mt-2">
                            * يتطلب إدخال مفتاح الخادم (Server Key) أولاً.
                        </div>
                    @endif
                </div>
            </div>

            <div class="ap-card mb-4">
                <div class="ap-card__header">
                    <h3 class="ap-card__title">دليل استخراج مفاتيح Firebase</h3>
                </div>
                <div class="ap-card__body" style="font-size: 13px; line-height: 1.6;">
                    <div class="mb-3 pb-3 border-bottom">
                        <h4 class="fw-bold text-dark fs-6 mb-1">الخيار 1: استخراج مفتاح الخادم (Server Key)</h4>
                        <ol class="ps-3 mb-0 text-muted">
                            <li>افتح <a href="https://console.firebase.google.com/" target="_blank" class="text-primary fw-medium">لوحة تحكم Firebase</a> واختر مشروع <strong>khademate2</strong>.</li>
                            <li>انقر على أيقونة الترس ⚙️ بجانب Project Overview ثم اختر <strong>Project Settings</strong>.</li>
                            <li>انتقل إلى تبويب <strong>Cloud Messaging</strong>.</li>
                            <li>ستجد <strong>Server key</strong>. إذا كان معطلاً، انقر على القائمة الثلاثية (⋮) واختر <strong>Manage API in Google Cloud Console</strong> ثم اضغط <strong>Enable</strong>.</li>
                            <li>انسخ المفتاح (يبدأ عادة بـ <code>AAAA...</code>) وضعه في حقل مفتاح الخادم أعلاه.</li>
                        </ol>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <h4 class="fw-bold text-dark fs-6 mb-1">الخيار 2: ملف الاعتماد (Service Account JSON)</h4>
                        <ol class="ps-3 mb-0 text-muted">
                            <li>في نفس لوحة تحكم Firebase، اذهب إلى <strong>Project Settings</strong>.</li>
                            <li>انتقل إلى تبويب <strong>Service accounts</strong>.</li>
                            <li>اضغط على زر <strong>Generate new private key</strong> ثم قم بتأكيد التحميل.</li>
                            <li>قم برفع الملف المحمّل مباشرة في خانة "رفع ملف JSON" أعلاه ثم اضغط حفظ.</li>
                        </ol>
                    </div>

                    <div>
                        <h4 class="fw-bold text-dark fs-6 mb-1">كيف تظهر في ستارة هاتف المشترك؟</h4>
                        <p class="text-muted mb-0">
                            عندما يزور المستخدم الموقع عبر الهاتف، يظهر له شريط يطلب تفعيل الإشعارات، بمجرد الضغط على "تفعيل الإشعارات" وتسجيل الإذن، ستصله أي أسعار جديدة أو إشعارات مباشرة في ستارة الإشعارات العليا حتى لو كان المتصفح مغلقاً، مع إصدار صوت ورجة اهتزاز.
                        </p>
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
