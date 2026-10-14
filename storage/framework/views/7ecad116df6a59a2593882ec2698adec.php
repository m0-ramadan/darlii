<?php $__env->startSection('title', 'تفاصيل الزائر #' . substr($visitor->visitor_id, 0, 8)); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="<?php echo e(route('admin.visitors.index')); ?>" class="btn btn-sm btn-outline-secondary">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'arrow-right', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <span>الرجوع للقائمة</span>
                </a>
                <h1 class="h4 fw-bold m-0">تفاصيل الزائر: #<?php echo e(substr($visitor->visitor_id, 0, 12)); ?></h1>
            </div>
            <p class="text-muted m-0 small">الموقع: <?php echo e($visitor->fullLocation()); ?> • الـ IP: <?php echo e($visitor->ip_address); ?></p>
        </div>
        <div>
            <form method="POST" action="<?php echo e(route('admin.visitors.destroy', $visitor)); ?>" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'trash-2', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <span>حذف السجل</span>
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Location & Device Cards -->
        <div class="col-12 col-lg-5">
            <!-- Geographical Location Card -->
            <div class="ap-card border-0 shadow-sm bg-white mb-4">
                <div class="ap-card__header border-bottom p-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span>الموقع الجغرافي والمركز</span>
                    </h5>
                    <?php if($visitor->location_source === 'gps'): ?>
                        <span class="badge bg-success bg-opacity-10 text-success">إحداثيات GPS مباشرة</span>
                    <?php else: ?>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">تحديد تقريبي عبر IP</span>
                    <?php endif; ?>
                </div>
                <div class="ap-card__body p-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">المحافظة</span>
                            <strong class="text-dark fs-6"><?php echo e($visitor->governorate ?: 'غير محددة'); ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">المركز / المدينة</span>
                            <strong class="text-primary fs-6"><?php echo e($visitor->city_or_markaz ?: 'غير محدد'); ?></strong>
                        </div>
                        <?php if($visitor->district): ?>
                            <div class="col-12">
                                <span class="text-muted small d-block mb-1">الحي / المنطقة</span>
                                <strong class="text-dark"><?php echo e($visitor->district); ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if($visitor->formatted_address): ?>
                            <div class="col-12">
                                <span class="text-muted small d-block mb-1">العنوان المنسق</span>
                                <div class="p-2 bg-light rounded small"><?php echo e($visitor->formatted_address); ?></div>
                            </div>
                        <?php endif; ?>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">خط العرض (Latitude)</span>
                            <code><?php echo e($visitor->latitude ?: '—'); ?></code>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">خط الطول (Longitude)</span>
                            <code><?php echo e($visitor->longitude ?: '—'); ?></code>
                        </div>

                        <?php if($visitor->googleMapsUrl()): ?>
                            <div class="col-12 mt-3">
                                <a href="<?php echo e($visitor->googleMapsUrl()); ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'external-link', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <span>فتح الموقع مباشرة على خرائط Google</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Device & Browser Card -->
            <div class="ap-card border-0 shadow-sm bg-white mb-4">
                <div class="ap-card__header border-bottom p-3">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'smartphone', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span>بيانات الجهاز والمتصفح</span>
                    </h5>
                </div>
                <div class="ap-card__body p-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">نوع الجهاز</span>
                            <strong class="text-dark text-capitalize"><?php echo e($visitor->device_type); ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">الماركة والموديل</span>
                            <strong class="text-dark"><?php echo e(trim(($visitor->device_brand ?? '') . ' ' . ($visitor->device_model ?? '')) ?: '—'); ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">نظام التشغيل</span>
                            <strong class="text-dark"><?php echo e($visitor->os ?: '—'); ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">المتصفح</span>
                            <strong class="text-dark"><?php echo e($visitor->browser ?: '—'); ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">أبعاد الشاشة</span>
                            <code><?php echo e($visitor->screen_resolution ?: '—'); ?></code>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">لغة المتصفح</span>
                            <code><?php echo e($visitor->browser_language ?: '—'); ?></code>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small d-block mb-1">نص الـ User Agent</span>
                            <div class="p-2 bg-light rounded text-muted" style="font-size: 11px; word-break: break-all;">
                                <?php echo e($visitor->user_agent); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Session details and Visited Pages History -->
        <div class="col-12 col-lg-7">
            <!-- Visit Overview Card -->
            <div class="ap-card border-0 shadow-sm bg-white mb-4">
                <div class="ap-card__header border-bottom p-3">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'activity', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span>معلومات الجلسة والتصفح</span>
                    </h5>
                </div>
                <div class="ap-card__body p-3">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">عنوان الـ IP</span>
                            <code class="fs-6"><?php echo e($visitor->ip_address); ?></code>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">المستخدم المسجل</span>
                            <?php if($visitor->user): ?>
                                <a href="<?php echo e(route('admin.users.edit', $visitor->user_id)); ?>" class="fw-bold text-primary">
                                    <?php echo e($visitor->user->name); ?> (<?php echo e($visitor->user->email); ?>)
                                </a>
                            <?php else: ?>
                                <span class="text-muted">زائر غير مسجل (Guest)</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">أول زيارة</span>
                            <strong><?php echo e($visitor->first_visited_at ? $visitor->first_visited_at->format('Y-m-d H:i') : '—'); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">آخر نشاط</span>
                            <strong><?php echo e($visitor->last_active_at ? $visitor->last_active_at->diffForHumans() : '—'); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">إجمالي الزيارات</span>
                            <span class="badge bg-primary px-3 py-2 fs-6"><?php echo e($visitor->visits_count); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">إجمالي الصفحات المستعرضة</span>
                            <span class="badge bg-info px-3 py-2 fs-6"><?php echo e($visitor->page_views_count); ?></span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small d-block mb-1">صفحة الدخول الأولى (Landing Page)</span>
                            <a href="<?php echo e($visitor->landing_page); ?>" target="_blank" class="small text-truncate d-block">
                                <?php echo e($visitor->landing_page ?: '—'); ?>

                            </a>
                        </div>
                        <?php if($visitor->referrer): ?>
                            <div class="col-12">
                                <span class="text-muted small d-block mb-1">مصدر الإحالة (Referrer)</span>
                                <span class="small text-muted"><?php echo e($visitor->referrer); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Page Hits Timeline -->
            <div class="ap-card border-0 shadow-sm bg-white">
                <div class="ap-card__header border-bottom p-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'clock', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span>سجل الصفحات التي زارها (آخر 50 صفحة)</span>
                    </h5>
                    <span class="badge bg-light text-muted border"><?php echo e($visitor->hits->count()); ?> سجل</span>
                </div>
                <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3 py-2">رابط الصفحة</th>
                                <th class="py-2" style="width: 140px;">الوقت</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $visitor->hits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-3">
                                        <a href="<?php echo e($hit->url); ?>" target="_blank" class="text-dark text-decoration-none small d-block text-truncate" style="max-width: 450px;" title="<?php echo e($hit->url); ?>">
                                            <?php echo e($hit->url); ?>

                                        </a>
                                        <?php if($hit->page_title): ?>
                                            <div class="text-muted" style="font-size: 11px;"><?php echo e($hit->page_title); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small text-muted"><?php echo e($hit->created_at ? $hit->created_at->format('H:i:s - Y/m/d') : '—'); ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted small">
                                        لا توجد صفحات إضافية مسجلة بعد.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/visitors/show.blade.php ENDPATH**/ ?>