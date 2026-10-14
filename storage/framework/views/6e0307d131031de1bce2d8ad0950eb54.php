<?php $__env->startSection('title', 'إضافة عقار جديد'); ?>

<?php $__env->startSection('content'); ?>
<style>
    :root {
        --dash-bg: #f8fafc;
        --dash-card: #ffffff;
        --dash-border: #e2e8f0;
        --dash-text: #0f172a;
        --dash-muted: #64748b;
        --dash-hover: #f1f5f9;
        --dash-hero: linear-gradient(180deg, #edf3f8 0%, #f8fafc 100%);
        --dash-primary: #db1d23;
        --dash-primary-hover: #b9151b;
        --dash-gold: #c8a45c;
    }

    html[data-theme="dark"] {
        --dash-bg: #0b1320;
        --dash-card: #111e33;
        --dash-border: #1e293b;
        --dash-text: #f8fafc;
        --dash-muted: #94a3b8;
        --dash-hover: #16243d;
        --dash-hero: linear-gradient(180deg, #0e192c 0%, #0b1320 100%);
    }

    .darli-dash-wrapper {
        background-color: var(--dash-bg);
        min-height: 80vh;
        padding-bottom: 80px;
    }

    .darli-dash-hero {
        background: var(--dash-hero);
        border-bottom: 1px solid var(--dash-border);
        padding: 40px 0 25px 0;
        margin-bottom: 30px;
    }

    .darli-dash-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--dash-muted);
        margin-bottom: 14px;
    }
    .darli-dash-breadcrumb a {
        color: var(--dash-muted);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .darli-dash-breadcrumb a:hover {
        color: var(--dash-primary);
    }
    .darli-dash-breadcrumb .sep {
        font-size: 11px;
        opacity: 0.6;
    }

    .darli-dash-title {
        font-size: 28px;
        font-weight: 800;
        color: var(--dash-text);
        margin: 0 0 6px 0;
        line-height: 1.3;
    }

    .darli-dash-subtitle {
        color: var(--dash-muted);
        font-size: 14.5px;
        margin: 0;
    }

    .darli-form-card {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        max-width: 860px;
        margin: 0 auto;
    }

    .darli-form-label {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: var(--dash-text);
        margin-bottom: 8px;
    }

    .darli-form-control,
    .darli-form-select {
        width: 100%;
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14.5px;
        color: var(--dash-text);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .darli-form-control:focus,
    .darli-form-select:focus {
        outline: none;
        border-color: var(--dash-primary);
        box-shadow: 0 0 0 3px rgba(219, 29, 35, 0.12);
    }

    .darli-alert-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        font-size: 14px;
    }

    .darli-btn-submit {
        background: var(--dash-primary);
        color: #ffffff;
        font-size: 16px;
        font-weight: 800;
        padding: 14px 28px;
        border-radius: 10px;
        border: none;
        width: 100%;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(219, 29, 35, 0.25);
        transition: all 0.2s ease;
    }
    .darli-btn-submit:hover {
        background: var(--dash-primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(219, 29, 35, 0.35);
    }
</style>

<div class="darli-dash-wrapper">
    
    <div class="darli-dash-hero">
        <div class="container">
            <div class="darli-dash-breadcrumb">
                <a href="<?php echo e(route('home')); ?>">الرئيسية</a>
                <span class="sep">›</span>
                <a href="<?php echo e(route('dashboard.index')); ?>">لوحة التحكم</a>
                <span class="sep">›</span>
                <a href="<?php echo e(route('dashboard.properties.index')); ?>">عقاراتي</a>
                <span class="sep">›</span>
                <span style="color: var(--dash-text); font-weight: 700;">إضافة عقار جديد</span>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="darli-dash-title">إضافة عقار جديد</h1>
                    <p class="darli-dash-subtitle">
                        أدخل بيانات وتفاصيل العقار لإرساله لمراجعة وتأكيد الإدارة قبل النشر بالموقع
                    </p>
                </div>
                <div>
                    <a href="<?php echo e(route('dashboard.properties.index')); ?>" class="btn btn-outline-secondary" style="border-radius: 8px; font-weight: 600; font-size: 13.5px;">
                        ← العودة إلى عقاراتي
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <div class="container">
        <div class="darli-form-card">

            
            <div class="darli-alert-warning">
                <span style="font-size: 24px; line-height: 1;">⏳</span>
                <div>
                    <strong style="color: #b45309;">تنويه هام:</strong>
                    عند إضافة العقار سيبقى في حسابك بحالة <strong>«قيد المراجعة»</strong> حتى يقوم فريق دارلي بمراجعة البيانات والتأكد منها، وفور الاعتماد سيتم نشره بالموقع فوراً ليظهر لآلاف الباحثين عن عقارات.
                </div>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger mb-4" style="border-radius: 10px;">
                    <ul class="mb-0 ps-3">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('dashboard.properties.store')); ?>" enctype="multipart/form-data" class="row g-3">
                <?php echo csrf_field(); ?>

                <div class="col-12">
                    <label class="darli-form-label">اسم / عنوان العقار <span class="text-danger">*</span></label>
                    <input class="darli-form-control" name="translations[ar][name]" value="<?php echo e(old('translations.ar.name')); ?>" placeholder="مثال: شقة تمليك 140م تشطيب سوبر لوكس بالحي الأول" required>
                </div>

                <div class="col-md-6">
                    <label class="darli-form-label">الغرض <span class="text-danger">*</span></label>
                    <select class="darli-form-select" name="purpose">
                        <option value="sale" <?php echo e(old('purpose') === 'sale' ? 'selected' : ''); ?>>للبيع</option>
                        <option value="rent" <?php echo e(old('purpose') === 'rent' ? 'selected' : ''); ?>>للإيجار</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="darli-form-label">السعر المطلوب (ج.م) <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="0" class="darli-form-control" name="price" value="<?php echo e(old('price')); ?>" placeholder="مثال: 1250000" required>
                    <input type="hidden" name="currency_id" value="<?php echo e($currencies->first()?->id ?? 1); ?>">
                </div>

                <div class="col-md-4">
                    <label class="darli-form-label">المساحة (م²)</label>
                    <input type="number" step="0.1" min="0" class="darli-form-control" name="area" value="<?php echo e(old('area')); ?>" placeholder="مثال: 140">
                </div>

                <div class="col-md-4">
                    <label class="darli-form-label">عدد الغرف</label>
                    <input class="darli-form-control" type="number" min="0" max="50" name="bedrooms" value="<?php echo e(old('bedrooms')); ?>" placeholder="3">
                </div>

                <div class="col-md-4">
                    <label class="darli-form-label">عدد الحمامات</label>
                    <input class="darli-form-control" type="number" min="0" max="20" name="bathrooms" value="<?php echo e(old('bathrooms')); ?>" placeholder="1">
                </div>

                <div class="col-12">
                    <label class="darli-form-label">تفاصيل ووصف العقار</label>
                    <textarea class="darli-form-control" rows="4" name="translations[ar][description]" placeholder="اكتب مواصفات العقار، الطابق، الخدمات المتوفرة، الموقع بالتحديد..."><?php echo e(old('translations.ar.description')); ?></textarea>
                </div>

                
                <div class="col-12 mt-4 pt-3" style="border-top: 1px dashed var(--dash-border);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span style="font-size: 22px;">📸</span>
                            <h6 class="fw-bold mb-0" style="color: var(--dash-text); font-size: 16.5px;">ألبوم ومعرض صور العقار (الصور الحقيقية)</h6>
                        </div>
                        <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 20px;">
                            ⭐ عنصر أساسي — العقارات المزودة بصور تحقق 8 أضعاف الاهتمام
                        </span>
                    </div>

                    
                    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1px solid #a7f3d0; border-radius: 12px; padding: 18px 20px; margin-bottom: 18px;">
                        <div class="d-flex align-items-start gap-3">
                            <div style="font-size: 22px; line-height: 1; flex-shrink: 0; margin-top: 2px;">💡</div>
                            <div style="font-size: 13.5px; color: #065f46; line-height: 1.7;">
                                <strong style="font-size: 14.5px; display: block; margin-bottom: 6px; color: #047857;">
                                    دليل وإرشادات رفع صور العقار للحصول على أسرع بيع / إيجار:
                                </strong>
                                <ul style="margin: 0; padding-right: 18px; display: grid; gap: 4px;">
                                    <li>
                                        <strong>صورة الغلاف الرئيسية:</strong> أول صورة ترفعها ستكون هي صورة الغلاف البارزة التي تظهر للزوار في نتائج البحث وبطاقة العقار الرئيسية.
                                    </li>
                                    <li>
                                        <strong>التقاط الصور نهاراً:</strong> يفضل تصوير الشقة نهاراً في ضوء طبيعي مع فتح النوافذ والستائر لإظهار المساحات الحقيقية بوضوح.
                                    </li>
                                    <li>
                                        <strong>تغطية كافة الأركان:</strong> صوّر واجهة العمارة والمدخل، الريسبشن من زاويتين واسعتين، غرف النوم، المطبخ، الحمامات، وأي إطلالة أو بلكونة مميزة.
                                    </li>
                                    <li>
                                        <strong>المواصفات الفنية:</strong> الصيغ المدعومة (<code>JPG</code>, <code>JPEG</code>, <code>PNG</code>, <code>WEBP</code>) والحد الأقصى لكل صورة هو <code>10 ميجابايت</code>.
                                    </li>
                                    <li>
                                        <strong>رفع متعدد دفعة واحدة:</strong> يمكنك تحديد عدة صور معاً من جهازك (بالضغط على Ctrl أو Shift أثناء الاختيار).
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    
                    <div id="prop-images-dropzone" style="border: 2px dashed #cbd5e1; border-radius: 14px; background: var(--dash-hover); padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.25s ease;">
                        <input type="file" id="prop-images-input" name="images[]" accept="image/jpeg,image/png,image/jpg,image/webp" multiple style="display: none;">
                        <div style="width: 60px; height: 60px; margin: 0 auto 12px; border-radius: 50%; background: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.06); font-size: 26px;">
                            🖼️
                        </div>
                        <h6 style="color: var(--dash-text); font-weight: 700; margin-bottom: 6px; font-size: 15px;">
                            اضغط هنا لاختيار صور العقار، أو اسحب الصور وأفلتها في هذه المساحة
                        </h6>
                        <p style="color: var(--dash-muted); font-size: 13px; margin-bottom: 14px;">
                            يمكنك اختيار صورة واحدة أو مجموعة صور معاً في نفس الوقت
                        </p>
                        <button type="button" id="btn-browse-images" class="btn" style="background: #0F3D2E; color: #ffffff; font-weight: 700; font-size: 13.5px; border-radius: 8px; padding: 9px 24px;">
                            📁 تصفح واختيار الصور من جهازك
                        </button>
                    </div>

                    
                    <div id="prop-images-preview-wrapper" style="display: none; margin-top: 18px;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2" style="border-bottom: 1px solid var(--dash-border);">
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-size: 16px;">📸</span>
                                <strong id="prop-images-count-text" style="color: var(--dash-text); font-size: 14px;">
                                    الصور المختارة للرفع
                                </strong>
                            </div>
                            <button type="button" id="btn-clear-all-images" class="btn btn-sm btn-outline-danger" style="font-size: 12px; border-radius: 6px;">
                                🗑️ إفراغ واختيار صور أخرى
                            </button>
                        </div>

                        
                        <div id="prop-images-preview-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 14px;"></div>
                    </div>
                </div>

                <div class="col-12">
                    <?php echo $__env->make('admin.partials.map-picker', [
                        'latId' => 'prop-latitude-user',
                        'lngId' => 'prop-longitude-user',
                        'latName' => 'latitude',
                        'lngName' => 'longitude',
                        'latValue' => old('latitude'),
                        'lngValue' => old('longitude'),
                        'containerId' => 'prop-user-map-box',
                        'mapId' => 'prop-user-map-leaflet',
                        'defaultLat' => 29.0724,
                        'defaultLng' => 31.0962,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                
                <div class="col-12 mt-4 pt-3" style="border-top: 1px dashed var(--dash-border);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="font-size: 20px;">📐</span>
                        <h6 class="fw-bold mb-0" style="color: var(--dash-text); font-size: 16px;">التخطيط الهندسي للشقة (المخطط المعماري)</h6>
                        <span class="badge" style="background: var(--dash-hover); color: var(--dash-muted); font-size: 11px;">اختياري</span>
                    </div>
                    <p style="font-size: 13px; color: var(--dash-muted); margin-bottom: 14px;">
                        أرفق رسم أو مخطط كروكي/هندسي للشقة يوضح تقسيم الغرف والمساحات والريسبشن لمساعدة المشترين على تصور الشقة بدقة.
                    </p>

                    <div style="background: var(--dash-hover); border: 1px solid var(--dash-border); border-radius: 12px; padding: 18px;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="darli-form-label" style="font-size: 13px;">عنوان أو اسم المخطط</label>
                                <input type="text" name="floor_plan_name" class="darli-form-control" value="<?php echo e(old('floor_plan_name')); ?>" placeholder="مثال: المخطط المعماري للشقة 140م">
                            </div>
                            <div class="col-md-6">
                                <label class="darli-form-label" style="font-size: 13px;">صورة التخطيط الهندسي</label>
                                <input type="file" name="floor_plan_image" class="darli-form-control" accept="image/*">
                                <small style="font-size: 11px; color: var(--dash-muted);">صيغ مدعومة: JPG, PNG, WEBP (بحد أقصى 10 ميجابايت)</small>
                            </div>
                            <div class="col-12">
                                <label class="darli-form-label" style="font-size: 13px;">ملاحظات معمارية أو وصف المخطط</label>
                                <textarea name="floor_plan_description" class="darli-form-control" rows="2" placeholder="مثال: مدخل خاص، ريسبشن 3 قطع، مطبخ أمريكان مفتوح، غرف نوم بحرية غير مجروحة..."><?php echo e(old('floor_plan_description')); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 pt-3">
                    <button type="submit" class="darli-btn-submit">
                        إرسال العقار للمراجعة والاعتماد ✓
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropzone = document.getElementById('prop-images-dropzone');
    const input = document.getElementById('prop-images-input');
    const browseBtn = document.getElementById('btn-browse-images');
    const previewWrapper = document.getElementById('prop-images-preview-wrapper');
    const previewGrid = document.getElementById('prop-images-preview-grid');
    const countText = document.getElementById('prop-images-count-text');
    const clearBtn = document.getElementById('btn-clear-all-images');

    if (!input || !dropzone) return;

    let dt = new DataTransfer();

    function openFilePicker(e) {
        if (e) e.stopPropagation();
        input.click();
    }

    dropzone.addEventListener('click', openFilePicker);
    if (browseBtn) {
        browseBtn.addEventListener('click', openFilePicker);
    }

    ['dragenter', 'dragover'].forEach(name => {
        dropzone.addEventListener(name, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = '#0F3D2E';
            dropzone.style.backgroundColor = 'rgba(15, 61, 46, 0.06)';
        });
    });

    ['dragleave', 'drop'].forEach(name => {
        dropzone.addEventListener(name, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.backgroundColor = 'var(--dash-hover)';
        });
    });

    dropzone.addEventListener('drop', function (e) {
        if (e.dataTransfer && e.dataTransfer.files) {
            handleFiles(e.dataTransfer.files);
        }
    });

    input.addEventListener('change', function () {
        if (input.files) {
            handleFiles(input.files);
        }
    });

    function handleFiles(files) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        const maxBytes = 10 * 1024 * 1024; // 10MB

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (!allowedTypes.includes(file.type)) {
                alert('الملف "' + file.name + '" ليس بصيغة مقبولة. الصيغ المدعومة هي JPG, PNG, WEBP فقط.');
                continue;
            }
            if (file.size > maxBytes) {
                alert('حجم الصورة "' + file.name + '" يتجاوز الحد الأقصى المسموح (10 ميجابايت).');
                continue;
            }

            let exists = false;
            for (let j = 0; j < dt.items.length; j++) {
                const existing = dt.items[j].getAsFile();
                if (existing && existing.name === file.name && existing.size === file.size) {
                    exists = true;
                    break;
                }
            }
            if (!exists) {
                dt.items.add(file);
            }
        }

        input.files = dt.files;
        renderPreviews();
    }

    function renderPreviews() {
        previewGrid.innerHTML = '';
        const files = dt.files;

        if (files.length === 0) {
            previewWrapper.style.display = 'none';
            return;
        }

        previewWrapper.style.display = 'block';
        countText.textContent = 'تم اختيار ' + files.length + ' صورة جاهزة للرفع مع العقار';

        Array.from(files).forEach((file, index) => {
            const item = document.createElement('div');
            item.style.position = 'relative';
            item.style.borderRadius = '10px';
            item.style.overflow = 'hidden';
            item.style.border = index === 0 ? '2px solid #c8a45c' : '1px solid var(--dash-border)';
            item.style.background = '#ffffff';
            item.style.aspectRatio = '4/3';
            item.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';

            const img = document.createElement('img');
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.alt = file.name;

            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);

            item.appendChild(img);

            // Primary badge on first image
            if (index === 0) {
                const badge = document.createElement('span');
                badge.textContent = '⭐ صورة الغلاف';
                badge.style.position = 'absolute';
                badge.style.bottom = '6px';
                badge.style.right = '6px';
                badge.style.background = '#0F3D2E';
                badge.style.color = '#ffffff';
                badge.style.fontSize = '10.5px';
                badge.style.fontWeight = '700';
                badge.style.padding = '3px 7px';
                badge.style.borderRadius = '4px';
                badge.style.boxShadow = '0 2px 4px rgba(0,0,0,0.25)';
                item.appendChild(badge);
            }

            // Remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.innerHTML = '✕';
            removeBtn.title = 'إلغاء هذه الصورة';
            removeBtn.style.position = 'absolute';
            removeBtn.style.top = '6px';
            removeBtn.style.left = '6px';
            removeBtn.style.background = 'rgba(0,0,0,0.65)';
            removeBtn.style.color = '#ffffff';
            removeBtn.style.border = 'none';
            removeBtn.style.borderRadius = '50%';
            removeBtn.style.width = '24px';
            removeBtn.style.height = '24px';
            removeBtn.style.fontSize = '12px';
            removeBtn.style.cursor = 'pointer';
            removeBtn.style.display = 'flex';
            removeBtn.style.alignItems = 'center';
            removeBtn.style.justifyContent = 'center';
            removeBtn.style.lineHeight = '1';

            removeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                removeFileAt(index);
            });

            item.appendChild(removeBtn);
            previewGrid.appendChild(item);
        });
    }

    function removeFileAt(indexToRemove) {
        const newDt = new DataTransfer();
        Array.from(dt.files).forEach((file, index) => {
            if (index !== indexToRemove) {
                newDt.items.add(file);
            }
        });
        dt = newDt;
        input.files = dt.files;
        renderPreviews();
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            dt = new DataTransfer();
            input.files = dt.files;
            renderPreviews();
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.public-shell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard/properties/create.blade.php ENDPATH**/ ?>