<?php $__env->startSection('title', 'إضافة عقار جديد'); ?>

<?php $__env->startSection('content'); ?>
<div class="container page-section" style="padding-top: 40px; padding-bottom: 70px; max-width: 900px;">
    <div class="mb-4">
        <a href="<?php echo e(route('dashboard.properties.index', ['locale' => app()->getLocale()])); ?>" class="text-decoration-none text-muted mb-2 d-inline-block" style="font-size: 14px;">
            &larr; العودة إلى عقاراتي
        </a>
        <h1 class="h3 fw-bold mb-1">إضافة عقار جديد</h1>
        <p class="text-muted mb-0" style="font-size: 14px;">أدخل بيانات وتفاصيل العقار لإرساله لمراجعة وتأكيد الإدارة قبل النشر</p>
    </div>

    
    <div class="p-3 mb-4 d-flex align-items-start gap-3" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 12px; font-size: 14px; color: #b45309;">
        <span style="font-size: 22px; line-height: 1;">⏳</span>
        <div>
            <strong style="color: #d97706;">تنويه هام للعملاء:</strong>
            عند إضافة العقار لن يظهر في نتائج البحث بالموقع مباشرة، بل سيبقى في حسابك بحالة <strong>«تحت المراجعة»</strong> حتى يقوم فريق الإدارة بمراجعة البيانات وتأكيدها، وفور الاعتماد سيتم نشره بالموقع فوراً.
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

    <div class="card shadow-sm border-0" style="border-radius: 16px; padding: 28px;">
        <form method="post" action="<?php echo e(route('dashboard.properties.store', ['locale' => app()->getLocale()])); ?>" class="row g-3">
            <?php echo csrf_field(); ?>

            <div class="col-12">
                <label class="form-label fw-bold">اسم / عنوان العقار <span class="text-danger">*</span></label>
                <input class="form-control" name="translations[ar][name]" value="<?php echo e(old('translations.ar.name')); ?>" placeholder="مثال: شقة تمليك 140م تشطيب سوبر لوكس بالحي الأول" required style="padding: 10px 14px; border-radius: 8px;">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">الغرض <span class="text-danger">*</span></label>
                <select class="form-select" name="purpose" style="padding: 10px 14px; border-radius: 8px;">
                    <option value="sale" <?php echo e(old('purpose') === 'sale' ? 'selected' : ''); ?>>للبيع</option>
                    <option value="rent" <?php echo e(old('purpose') === 'rent' ? 'selected' : ''); ?>>للإيجار</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">السعر المطلوب (ج.م) <span class="text-danger">*</span></label>
                <input type="number" step="1" min="0" class="form-control" name="price" value="<?php echo e(old('price')); ?>" placeholder="مثال: 1250000" required style="padding: 10px 14px; border-radius: 8px;">
                <input type="hidden" name="currency_id" value="<?php echo e($currencies->first()?->id ?? 1); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">المساحة (م²)</label>
                <input type="number" step="0.1" min="0" class="form-control" name="area" value="<?php echo e(old('area')); ?>" placeholder="مثال: 120" style="padding: 10px 14px; border-radius: 8px;">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">عدد الغرف</label>
                <input class="form-control" type="number" min="0" max="50" name="bedrooms" value="<?php echo e(old('bedrooms')); ?>" placeholder="3" style="padding: 10px 14px; border-radius: 8px;">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">عدد الحمامات</label>
                <input class="form-control" type="number" min="0" max="20" name="bathrooms" value="<?php echo e(old('bathrooms')); ?>" placeholder="1" style="padding: 10px 14px; border-radius: 8px;">
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">تفاصيل ووصف العقار</label>
                <textarea class="form-control" rows="5" name="translations[ar][description]" placeholder="اكتب مواصفات العقار، الطابق، الخدمات المتوفرة، الموقع بالتحديد..." style="border-radius: 8px;"><?php echo e(old('translations.ar.description')); ?></textarea>
            </div>

            <div class="col-12 pt-3">
                <button type="submit" class="btn btn-primary w-100" style="background: #C49A45; border-color: #C49A45; font-weight: 600; padding: 13px; font-size: 16px; border-radius: 10px;">
                    إرسال العقار للمراجعة والاعتماد
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard/properties/create.blade.php ENDPATH**/ ?>