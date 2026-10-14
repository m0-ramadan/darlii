عايز امسك بالماوس واسكرول بيه @extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('page_actions')
    <a href="{{ admin_route('admin.notifications.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 15])
        العودة لسجل الإشعارات
    </a>
@endsection

@section('content')
    <div class="row g-4">
        {{-- Notification Form --}}
        <div class="col-lg-7">
            <div class="ap-card">
                <div class="ap-card__header d-flex align-items-center justify-content-between">
                    <h2 class="ap-card__title">بيانات الإشعار الجديد</h2>
                    <span class="badge bg-primary bg-opacity-10 text-primary">Web Push & In-App</span>
                </div>
                <div class="ap-card__body">
                    <form method="POST" action="{{ admin_route('admin.notifications.store') }}"
                        enctype="multipart/form-data" id="notificationForm">
                        @csrf

                        {{-- Target Audience --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark d-block">الجمهور المستهدف <span
                                    class="text-danger">*</span></label>
                            <div class="d-flex gap-3 flex-wrap">
                                <label
                                    class="border rounded-3 p-3 d-flex align-items-center gap-2 flex-grow-1 cursor-pointer target-pill bg-light"
                                    style="cursor: pointer;">
                                    <input type="radio" name="target_type" value="all" id="target_all"
                                        class="form-check-input mt-0"
                                        {{ old('target_type', 'all') === 'all' ? 'checked' : '' }}>
                                    <div>
                                        <div class="fw-bold text-dark">عام للجميع (بث شامل)</div>
                                        <div class="text-muted small">إرسال لجميع المشتركين وزوار الموقع المسموح لهم
                                            باستقبال الإشعارات</div>
                                    </div>
                                </label>

                                <label
                                    class="border rounded-3 p-3 d-flex align-items-center gap-2 flex-grow-1 cursor-pointer target-pill bg-light"
                                    style="cursor: pointer;">
                                    <input type="radio" name="target_type" value="user" id="target_user"
                                        class="form-check-input mt-0" {{ old('target_type') === 'user' ? 'checked' : '' }}>
                                    <div>
                                        <div class="fw-bold text-dark">شخص محدد (مستخدم / عميل / وكيل)</div>
                                        <div class="text-muted small">إرسال إشعار خاص وموجه لشخص واحد فقط في حسابه</div>
                                    </div>
                                </label>
                            </div>
                            @error('target_type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Specific User Select (Visible if target_type == user) --}}
                        <div class="mb-4" id="userSelectWrapper"
                            style="{{ old('target_type', 'all') === 'user' ? '' : 'display: none;' }}">
                            <label for="user_id" class="form-label fw-bold text-dark">اختر المستخدم المستهدف <span
                                    class="text-danger">*</span></label>
                            <select name="user_id" id="user_id"
                                class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">-- ابحث أو اختر المستخدم --</option>
                                @foreach ($users as $userItem)
                                    <option value="{{ $userItem->id }}"
                                        {{ old('user_id') == $userItem->id ? 'selected' : '' }}>
                                        {{ $userItem->name }}
                                        ({{ $userItem->email }})
                                        {{ $userItem->job_title ? ' - ' . $userItem->job_title : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">اختر المستخدم الذي تود توجيه الإشعار إليه حصرياً.</div>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Title --}}
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold text-dark">عنوان الإشعار <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" id="title"
                                class="form-control form-control-lg @error('title') is-invalid @enderror"
                                placeholder="مثال: خصم استثنائي 10% على وحدات كمبوند دارلي!" value="{{ old('title') }}"
                                required maxlength="255">
                            <div class="form-text">العنوان البارز الذي يظهر أعلى نافذة الإشعار في الهاتف أو شاشة الكمبيوتر.
                            </div>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Body --}}
                        <div class="mb-3">
                            <label for="body" class="form-label fw-bold text-dark">نص الإشعار <span
                                    class="text-danger">*</span></label>
                            <textarea name="body" id="body" rows="4" class="form-control @error('body') is-invalid @enderror"
                                placeholder="اكتب تفاصيل الإشعار هنا بشكل جذاب ومشجع على الضغط..." required maxlength="1000">{{ old('body') }}</textarea>
                            <div class="form-text">النص التوضيحي الذي يشرح تفاصيل الخبر أو العرض للمستخدم.</div>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Image Options --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark d-block">إرفاق صورة مع الإشعار (اختياري)</label>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">رفع صورة من الجهاز:</label>
                                    <input type="file" name="image_file" id="image_file"
                                        class="form-control @error('image_file') is-invalid @enderror" accept="image/*">
                                    @error('image_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">أو رابط صورة خارجي مباشر:</label>
                                    <input type="url" name="image_url" id="image_url"
                                        class="form-control @error('image_url') is-invalid @enderror"
                                        placeholder="https://example.com/banner.jpg" value="{{ old('image_url') }}">
                                    @error('image_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-text">تظهر الصورة بشكل بارز في الإشعار الفوري وتجذب انتباه العملاء بنسبة تزيد
                                عن 80%.</div>
                        </div>

                        {{-- Action URL --}}
                        <div class="mb-4">
                            <label for="action_url" class="form-label fw-bold text-dark">رابط التوجيه عند الضغط على الإشعار
                                (اختياري)</label>
                            <input type="text" name="action_url" id="action_url"
                                class="form-control @error('action_url') is-invalid @enderror"
                                placeholder="{{ url('/ar/properties') }}" value="{{ old('action_url', url('/ar')) }}">
                            <div class="form-text">الصفحة التي سيتم فتحها تلقائياً للمستخدم فور نقره على الإشعار.</div>
                            @error('action_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                            <a href="{{ admin_route('admin.notifications.index') }}"
                                class="btn btn-outline-secondary">إلغاء</a>
                            <button type="submit" class="ap-btn ap-btn--primary px-4 py-2">
                                @include('admin.partials.icon', ['iconName' => 'send', 'size' => 16])
                                إرسال وبث الإشعار الآن
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Live Preview Card --}}
        <div class="col-lg-5">
            <div class="sticky-top" style="top: 20px;">
                <div class="ap-card mb-4">
                    <div class="ap-card__header">
                        <h3 class="ap-card__title">معاينة حية لشكل الإشعار</h3>
                        <p class="ap-card__subtitle mb-0">هكذا سيظهر الإشعار على شاشات الزوار والمشتركين فور إرساله</p>
                    </div>
                    <div class="ap-card__body bg-light">
                        {{-- Desktop Push Simulation --}}
                        <div class="text-muted small fw-bold mb-2">إشعار سطح المكتب / المتصفح:</div>
                        <div class="bg-white rounded-3 shadow-sm border p-3 mb-4" style="direction: rtl;">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ asset('themes/homzen/images/logo/logo-darli.png') }}" alt="Darlii"
                                        class="rounded" style="width: 22px; height: 22px; object-fit: contain;">
                                    <span class="fw-bold small text-dark">منصة دارلي العقارية</span>
                                </div>
                                <span class="text-muted" style="font-size: 11px;">الآن • Chrome</span>
                            </div>
                            <div class="d-flex gap-3 align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark mb-1" id="previewTitle" style="font-size: 14px;">عنوان
                                        الإشعار يظهر هنا</div>
                                    <div class="text-muted small" id="previewBody"
                                        style="font-size: 12px; line-height: 1.4;">نص وتفاصيل الإشعار تظهر هنا بشكل أنيق
                                        وواضح ومحفز للزيارة...</div>
                                </div>
                                <img src="{{ asset('themes/homzen/images/logo/logo-darli.png') }}" id="previewThumb"
                                    alt="preview" class="rounded border"
                                    style="width: 50px; height: 50px; object-fit: cover; display: none;">
                            </div>
                            <div id="previewLargeImgWrapper" class="mt-2 pt-2 border-top" style="display: none;">
                                <img src="" id="previewLargeImg" alt="banner" class="w-100 rounded border"
                                    style="max-height: 160px; object-fit: cover;">
                            </div>
                        </div>

                        {{-- Mobile Push Simulation --}}
                        <div class="text-muted small fw-bold mb-2">إشعار شاشات الهاتف المحمول:</div>
                        <div class="bg-white rounded-4 shadow-sm border p-3"
                            style="max-width: 320px; margin: 0 auto; direction: rtl;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-1 d-flex align-items-center justify-content-center"
                                        style="width: 22px; height: 22px;">
                                        @include('admin.partials.icon', [
                                            'iconName' => 'bell',
                                            'size' => 12,
                                        ])
                                    </div>
                                    <span class="fw-bold text-dark" style="font-size: 12px;">دارلي العقارية</span>
                                </div>
                                <span class="text-muted" style="font-size: 10px;">الآن</span>
                            </div>
                            <div class="fw-bold text-dark mb-1" id="mobilePreviewTitle" style="font-size: 13px;">عنوان
                                الإشعار يظهر هنا</div>
                            <div class="text-muted" id="mobilePreviewBody" style="font-size: 11px; line-height: 1.3;">نص
                                وتفاصيل الإشعار تظهر هنا...</div>
                            <div id="mobilePreviewImgWrapper" class="mt-2" style="display: none;">
                                <img src="" id="mobilePreviewImg" alt="banner" class="w-100 rounded-3 border"
                                    style="max-height: 120px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Guide Box --}}
                <div class="ap-card border-info border-opacity-25 bg-info bg-opacity-10">
                    <div class="ap-card__body p-3">
                        <div class="d-flex gap-2">
                            <div class="text-info">
                                @include('admin.partials.icon', [
                                    'iconName' => 'help-circle',
                                    'size' => 20,
                                ])
                            </div>
                            <div class="small">
                                <strong class="d-block text-dark mb-1">نصائح لإشعار جذاب وفعال:</strong>
                                <ul class="mb-0 ps-3 text-muted">
                                    <li>اجعل العنوان قصيراً وواضحاً (أقل من 50 حرفاً).</li>
                                    <li>استخدم صوراً أفقية بنسبة 16:9 أو مربعة بحجم واضح.</li>
                                    <li>حدد رابط توجيه مباشر للعقار أو الصفحة المعنية.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Target radio toggle
            const targetAll = document.getElementById('target_all');
            const targetUser = document.getElementById('target_user');
            const userSelectWrapper = document.getElementById('userSelectWrapper');

            function updateTargetView() {
                if (targetUser.checked) {
                    userSelectWrapper.style.display = 'block';
                } else {
                    userSelectWrapper.style.display = 'none';
                }
            }

            targetAll.addEventListener('change', updateTargetView);
            targetUser.addEventListener('change', updateTargetView);

            // Live preview elements
            const titleInput = document.getElementById('title');
            const bodyInput = document.getElementById('body');
            const imageUrlInput = document.getElementById('image_url');
            const imageFileInput = document.getElementById('image_file');

            const previewTitle = document.getElementById('previewTitle');
            const previewBody = document.getElementById('previewBody');
            const mobilePreviewTitle = document.getElementById('mobilePreviewTitle');
            const mobilePreviewBody = document.getElementById('mobilePreviewBody');

            const previewThumb = document.getElementById('previewThumb');
            const previewLargeImgWrapper = document.getElementById('previewLargeImgWrapper');
            const previewLargeImg = document.getElementById('previewLargeImg');
            const mobilePreviewImgWrapper = document.getElementById('mobilePreviewImgWrapper');
            const mobilePreviewImg = document.getElementById('mobilePreviewImg');

            function updateText() {
                const titleVal = titleInput.value.trim() || 'عنوان الإشعار يظهر هنا';
                const bodyVal = bodyInput.value.trim() ||
                    'نص وتفاصيل الإشعار تظهر هنا بشكل أنيق وواضح ومحفز للزيارة...';

                previewTitle.textContent = titleVal;
                mobilePreviewTitle.textContent = titleVal;

                previewBody.textContent = bodyVal;
                mobilePreviewBody.textContent = bodyVal;
            }

            function setImage(src) {
                if (src) {
                    previewThumb.src = src;
                    previewThumb.style.display = 'block';

                    previewLargeImg.src = src;
                    previewLargeImgWrapper.style.display = 'block';

                    mobilePreviewImg.src = src;
                    mobilePreviewImgWrapper.style.display = 'block';
                } else {
                    previewThumb.style.display = 'none';
                    previewLargeImgWrapper.style.display = 'none';
                    mobilePreviewImgWrapper.style.display = 'none';
                }
            }

            titleInput.addEventListener('input', updateText);
            bodyInput.addEventListener('input', updateText);

            imageUrlInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    setImage(this.value.trim());
                } else if (!imageFileInput.files || !imageFileInput.files[0]) {
                    setImage(null);
                }
            });

            imageFileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        setImage(e.target.result);
                    };
                    reader.readAsDataURL(this.files[0]);
                } else if (imageUrlInput.value.trim()) {
                    setImage(imageUrlInput.value.trim());
                } else {
                    setImage(null);
                }
            });

            updateText();
            if (imageUrlInput.value.trim()) {
                setImage(imageUrlInput.value.trim());
            }
        });
    </script>
@endpush
