<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $user = auth()->user();
    $isPending = $user?->status === 'pending';
?>

<?php $__env->startSection('title', 'عقاراتي المسجلة'); ?>

<?php $__env->startPush('head'); ?>
    <style>
        @media (max-width: 576px) {
            .dashboard-properties-title {
                font-size: 22px !important;
                line-height: 1.35;
            }

            .dashboard-properties-subtitle {
                display: none;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container page-section" style="padding-top: 35px; padding-bottom: 60px;">
        <?php if(session('success')): ?>
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert"
                style="border-radius: 12px; background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
                <div><strong>✓ تم بنجاح:</strong> <?php echo e(session('success')); ?></div>
            </div>
        <?php endif; ?>

        <?php if(session('status_warning') || $isPending): ?>
            <div class="alert alert-warning mb-4 shadow-sm" role="alert"
                style="border-radius: 12px; background: #FFFBEB; border-color: #FDE68A; color: #92400E; padding: 16px 20px;">
                ⏳ <strong>حسابك قيد المراجعة والاعتماد:</strong> مرحباً بك في دارلي! حسابك حالياً قيد المراجعة والاعتماد من قبل إدارة المنصة. بمجرد تفعيل الحساب ستتمكن من إضافة ونشر العقارات مباشرة.
            </div>
        <?php endif; ?>

        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="<?php echo e(route('dashboard.index', ['locale' => $locale])); ?>" class="text-muted text-decoration-none" style="font-size: 13px;">لوحة التحكم</a>
                    <span class="text-muted">/</span>
                    <span class="fw-bold" style="font-size: 13px;">عقاراتي</span>
                </div>
                <h1 class="h3 fw-bold mb-1 dashboard-properties-title">عقاراتي المسجلة والمتابعة</h1>
                <p class="text-muted mb-0 dashboard-properties-subtitle" style="font-size: 14px;">متابعة تفاصيل العقارات والزيارات وحالات البيع لكل وحدة</p>
            </div>

            <div class="d-flex gap-2">
                <?php if($isPending): ?>
                    <button class="btn btn-secondary disabled" style="opacity: 0.65; border-radius: 8px; font-weight: 600;">
                        + إضافة عقار جديد (بانتظار التفعيل)
                    </button>
                <?php else: ?>
                    <a class="btn btn-primary" href="<?php echo e(route('dashboard.properties.create', ['locale' => $locale])); ?>"
                        style="background: #db1d23; border-color: #db1d23; font-weight: 700; padding: 10px 22px; border-radius: 8px;">
                        + إضافة عقار جديد
                    </a>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <a href="<?php echo e(route('dashboard.properties.index', ['locale' => $locale])); ?>"
               class="btn btn-sm <?php if(!request('status')): ?> btn-dark <?php else: ?> btn-outline-secondary <?php endif; ?>" style="border-radius: 20px; font-size: 13px; padding: 6px 16px;">
                الكل (<?php echo e($properties->total()); ?>)
            </a>
            <a href="<?php echo e(route('dashboard.properties.index', ['locale' => $locale, 'status' => 'published'])); ?>"
               class="btn btn-sm <?php if(request('status') === 'published'): ?> btn-success <?php else: ?> btn-outline-success <?php endif; ?>" style="border-radius: 20px; font-size: 13px; padding: 6px 16px;">
                ✓ معروضة بالموقع
            </a>
            <a href="<?php echo e(route('dashboard.properties.index', ['locale' => $locale, 'status' => 'sold'])); ?>"
               class="btn btn-sm <?php if(request('status') === 'sold'): ?> btn-info text-white <?php else: ?> btn-outline-info <?php endif; ?>" style="border-radius: 20px; font-size: 13px; padding: 6px 16px;">
                🤝 تم البيع
            </a>
            <a href="<?php echo e(route('dashboard.properties.index', ['locale' => $locale, 'status' => 'pending_review'])); ?>"
               class="btn btn-sm <?php if(request('status') === 'pending_review'): ?> btn-warning <?php else: ?> btn-outline-warning text-dark <?php endif; ?>" style="border-radius: 20px; font-size: 13px; padding: 6px 16px;">
                ⏳ قيد المراجعة
            </a>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                    <thead style="background: #F8FAFC;">
                        <tr>
                            <th style="padding: 14px 18px;">كود العقار</th>
                            <th>اسم العقار والموقع</th>
                            <th>السعر</th>
                            <th>الزيارات (المشاهدات)</th>
                            <th>الحالة بالموقع</th>
                            <th class="text-end" style="padding-left: 18px;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $st = $p->status?->value ?? (string) $p->status;
                            ?>
                            <tr>
                                <td style="padding: 14px 18px;">
                                    <code><?php echo e($p->reference_code); ?></code>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo e($p->translation()?->name ?: 'عقار بدون اسم'); ?></div>
                                    <div class="text-muted" style="font-size: 12px;">
                                        <?php echo e($p->location?->translation()?->name ?? '—'); ?>

                                    </div>
                                </td>
                                <td>
                                    <?php if($p->isPriceOnContact()): ?>
                                        <span class="badge text-bg-secondary">عند التواصل</span>
                                    <?php else: ?>
                                        <span class="fw-bold"><?php echo e(money_eg($p->price)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="d-inline-flex align-items-center gap-1 fw-bold text-primary" style="font-size: 13.5px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <?php echo e(number_format($p->views_count)); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($st === 'pending_review'): ?>
                                        <span class="badge"
                                            style="background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            ⏳ تحت المراجعة
                                        </span>
                                    <?php elseif($st === 'published'): ?>
                                        <span class="badge"
                                            style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            ✓ معتمد ومنشور
                                        </span>
                                    <?php elseif($st === 'sold'): ?>
                                        <span class="badge"
                                            style="background: #CCFBF1; color: #0F766E; border: 1px solid #99F6E4; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            🤝 تم البيع
                                        </span>
                                    <?php elseif($st === 'rejected'): ?>
                                        <span class="badge"
                                            style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            ✕ مرفوض
                                        </span>
                                    <?php else: ?>
                                        <span class="badge"
                                            style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            <?php echo e($st); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end" style="padding-left: 18px;">
                                    <div class="d-inline-flex gap-1">
                                        <?php if($st === 'draft' || $st === 'rejected'): ?>
                                            <form method="post"
                                                action="<?php echo e(route('dashboard.properties.submit', ['locale' => $locale, 'property' => $p])); ?>"
                                                style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-sm btn-outline-primary"
                                                    style="border-radius: 6px; font-size: 12px;">إرسال للمراجعة</button>
                                            </form>
                                        <?php elseif($st === 'pending_review'): ?>
                                            <span class="text-muted" style="font-size: 12px;">بانتظار موافقة الإدارة</span>
                                        <?php elseif($st === 'published'): ?>
                                            <a href="<?php echo e(route('properties.show', ['locale' => $locale, 'slug' => $p->translation()?->slug ?? $p->id])); ?>"
                                                class="btn btn-sm btn-outline-success" target="_blank"
                                                style="border-radius: 6px; font-size: 12px;">
                                                عرض بالموقع ↗
                                            </a>
                                            <form method="POST" action="<?php echo e(route('dashboard.properties.sold', ['locale' => $locale, 'property' => $p])); ?>" style="display:inline;" onsubmit="return confirm('هل تريد تحديد هذا العقار كـ «تم البيع»؟');">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-teal" style="border: 1px solid #0d9488; color: #0d9488; border-radius: 6px; font-size: 12px;">
                                                    تم البيع ✓
                                                </button>
                                            </form>
                                        <?php elseif($st === 'sold'): ?>
                                            <form method="POST" action="<?php echo e(route('dashboard.properties.available', ['locale' => $locale, 'property' => $p])); ?>" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; font-size: 12px;">
                                                    إعادة العرض
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    لا توجد عقارات مسجلة لديك تطابق هذا الفلتر.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($properties->hasPages()): ?>
            <div class="mt-4">
                <?php echo e($properties->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard/properties/index.blade.php ENDPATH**/ ?>