<?php $__env->startSection('title', 'سجل الزوار والمواقع الجغرافية'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 28], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <span>سجل الزوار والمواقع الجغرافية</span>
            </h1>
            <p class="text-muted m-0">تتبع تفصيلي لجميع زوار المنصة، المحافظات، المراكز، الأجهزة والمتصفحات لحظة بلحظة</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.visitors.index')); ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'rotate-cw', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <span>تحديث</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ap-card border-0 shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">إجمالي الزوار المسجلين</div>
                        <div class="fs-3 fw-bold text-dark"><?php echo e(number_format($totalVisitors)); ?></div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'users', 'size' => 24], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ap-card border-0 shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">زوار اليوم</div>
                        <div class="fs-3 fw-bold text-success"><?php echo e(number_format($todayVisitors)); ?></div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'activity', 'size' => 24], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ap-card border-0 shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">أعلى المحافظات زيارة</div>
                        <div class="fs-5 fw-bold text-dark text-truncate" title="<?php echo e($topGovernorate); ?>"><?php echo e($topGovernorate); ?></div>
                        <div class="small text-muted text-truncate" title="<?php echo e($topMarkaz); ?>">المركز الأكثر: <?php echo e($topMarkaz); ?></div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 24], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ap-card border-0 shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">مستخدمو الموبايل</div>
                        <div class="fs-3 fw-bold text-info"><?php echo e($mobilePercentage); ?>%</div>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'smartphone', 'size' => 24], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="ap-card border-0 shadow-sm bg-white mb-4">
        <div class="ap-card__body p-3">
            <form method="GET" action="<?php echo e(route('admin.visitors.index')); ?>" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'search', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </span>
                        <input type="text" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" class="form-control border-start-0" placeholder="بحث بالـ IP أو المعرّف أو المستخدم...">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <select name="governorate" class="form-select">
                        <option value="">جميع المحافظات</option>
                        <?php $__currentLoopData = $availableGovernorates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($gov); ?>" <?php echo e(($filters['governorate'] ?? '') === $gov ? 'selected' : ''); ?>><?php echo e($gov); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                    <input type="text" name="markaz" value="<?php echo e($filters['markaz'] ?? ''); ?>" class="form-control" placeholder="فلترة بالمركز (مثال: ناصر)...">
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                    <select name="device_type" class="form-select">
                        <option value="">كل الأجهزة</option>
                        <option value="mobile" <?php echo e(($filters['device_type'] ?? '') === 'mobile' ? 'selected' : ''); ?>>هواتف (Mobile)</option>
                        <option value="desktop" <?php echo e(($filters['device_type'] ?? '') === 'desktop' ? 'selected' : ''); ?>>كمبيوتر (Desktop)</option>
                        <option value="tablet" <?php echo e(($filters['device_type'] ?? '') === 'tablet' ? 'selected' : ''); ?>>أجهزة لوحية (Tablet)</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">تطبيق</button>
                    <?php if(!empty(array_filter($filters))): ?>
                        <a href="<?php echo e(route('admin.visitors.index')); ?>" class="btn btn-outline-secondary" title="إلغاء الفلاتر">
                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'x', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Visitors Table -->
    <div class="ap-card border-0 shadow-sm bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3" style="width: 220px;">الزائر / المعرّف</th>
                        <th class="py-3" style="width: 240px;">المحافظة والمركز</th>
                        <th class="py-3" style="width: 200px;">الجهاز والمتصفح</th>
                        <th class="py-3">الصفحة والتصفح</th>
                        <th class="py-3" style="width: 140px;">عنوان الـ IP</th>
                        <th class="py-3" style="width: 160px;">آخر نشاط</th>
                        <th class="pe-3 text-end py-3" style="width: 100px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $visitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visitor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <!-- Visitor ID & User -->
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-muted fw-bold" style="width: 38px; height: 38px; font-size: 13px;">
                                        <?php echo e(substr($visitor->visitor_id, 0, 2)); ?>

                                    </div>
                                    <div class="overflow-hidden">
                                        <?php if($visitor->user): ?>
                                            <a href="<?php echo e(route('admin.users.edit', $visitor->user_id)); ?>" class="fw-bold text-dark d-block text-truncate text-decoration-none">
                                                <?php echo e($visitor->user->name); ?>

                                            </a>
                                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 11px;">عضو مسجل</span>
                                        <?php else: ?>
                                            <span class="fw-semibold text-dark d-block text-truncate" title="<?php echo e($visitor->visitor_id); ?>">
                                                زائر #<?php echo e(substr($visitor->visitor_id, 0, 8)); ?>

                                            </span>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size: 11px;">زائر الموقع</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Governorate & Markaz -->
                            <td>
                                <div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1" style="font-size: 13px;">
                                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 12], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <?php echo e($visitor->governorate ?: 'مصر'); ?>

                                    </span>
                                    <?php if($visitor->city_or_markaz): ?>
                                        <div class="fw-semibold text-dark mt-1" style="font-size: 13px;">
                                            <?php echo e($visitor->city_or_markaz); ?>

                                        </div>
                                    <?php endif; ?>
                                    <?php if($visitor->district): ?>
                                        <div class="small text-muted" style="font-size: 11px;">
                                            <?php echo e($visitor->district); ?>

                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <?php if($visitor->location_source === 'gps'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 10px;" title="تم التحديد عبر إحداثيات GPS من المتصفح بدقة">
                                                دقة GPS عالية
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border" style="font-size: 10px;" title="تم التحديد تقريبياً عبر عنوان IP الشبكة">
                                                تحديد IP
                                            </span>
                                        <?php endif; ?>

                                        <?php if($visitor->googleMapsUrl()): ?>
                                            <a href="<?php echo e($visitor->googleMapsUrl()); ?>" target="_blank" class="small text-primary text-decoration-none" title="فتح الموقع على خرائط جوجل">
                                                الخريطة ↗
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Device & Browser -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-secondary">
                                        <?php if($visitor->device_type === 'mobile'): ?>
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'smartphone', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <?php elseif($visitor->device_type === 'tablet'): ?>
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'tablet', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <?php else: ?>
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'monitor', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <?php endif; ?>
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size: 13px;">
                                            <?php echo e($visitor->browser ?: 'متصفح ويب'); ?>

                                        </div>
                                        <div class="small text-muted" style="font-size: 11px;">
                                            <?php echo e($visitor->os ?: 'نظام غير محدد'); ?>

                                            <?php if($visitor->screen_resolution): ?>
                                                (<?php echo e($visitor->screen_resolution); ?>)
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Page & Browsing -->
                            <td>
                                <div class="text-truncate" style="max-width: 280px;" title="<?php echo e($visitor->last_page); ?>">
                                    <a href="<?php echo e($visitor->last_page ?: '#'); ?>" target="_blank" class="text-dark text-decoration-none small">
                                        <?php echo e($visitor->last_page ?: 'الصفحة الرئيسية'); ?>

                                    </a>
                                </div>
                                <div class="small text-muted mt-1 d-flex gap-2">
                                    <span><?php echo e($visitor->visits_count); ?> زيارة</span>
                                    <span>•</span>
                                    <span><?php echo e($visitor->page_views_count); ?> صفحة</span>
                                    <?php if($visitor->referrer): ?>
                                        <span>•</span>
                                        <span class="text-truncate" style="max-width: 140px;" title="<?php echo e($visitor->referrer); ?>">من: <?php echo e(parse_url($visitor->referrer, PHP_URL_HOST) ?: 'مباشر'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- IP Address -->
                            <td>
                                <code class="small text-dark"><?php echo e($visitor->ip_address); ?></code>
                            </td>

                            <!-- Last Active -->
                            <td>
                                <div class="small fw-semibold text-dark">
                                    <?php echo e($visitor->last_active_at ? $visitor->last_active_at->diffForHumans() : 'غير محدد'); ?>

                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    <?php echo e($visitor->last_active_at ? $visitor->last_active_at->format('Y-m-d H:i') : ''); ?>

                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="pe-3 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <a href="<?php echo e(route('admin.visitors.show', $visitor)); ?>" class="btn btn-sm btn-light text-primary" title="عرض تفاصيل الزائر وسجل نشاطه">
                                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'eye', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </a>
                                    <form method="POST" action="<?php echo e(route('admin.visitors.destroy', $visitor)); ?>" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="حذف">
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'trash-2', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-2">
                                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 48], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                                <h5>لا توجد سجلات زوار تطابق معايير البحث</h5>
                                <p class="small text-muted">سيتم تسجيل زوار الموقع تلقائياً بمجرد دخولهم للمنصة.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($visitors->hasPages()): ?>
            <div class="p-3 border-top">
                <?php echo e($visitors->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/visitors/index.blade.php ENDPATH**/ ?>