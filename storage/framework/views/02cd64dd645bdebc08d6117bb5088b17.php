<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('page_title', $title); ?>
<?php $__env->startSection('page_subtitle', $subtitle); ?>

<?php $__env->startSection('page_actions'); ?>
    <div class="d-flex gap-2">
        <a href="<?php echo e(admin_route('admin.notifications.settings')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'settings', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            إعدادات Firebase
        </a>
        <a href="<?php echo e(admin_route('admin.notifications.create')); ?>" class="ap-btn ap-btn--primary ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'send', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            إرسال إشعار جديد
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="ap-tabs mb-4">
        <a href="<?php echo e(admin_route('admin.notifications.index')); ?>" class="ap-tab is-active">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'bell', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            سجل الإشعارات المرسلة (<?php echo e($stats['total_sent']); ?>)
        </a>
        <a href="<?php echo e(admin_route('admin.notifications.create')); ?>" class="ap-tab">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'send', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            إرسال إشعار جديد
        </a>
        <a href="<?php echo e(admin_route('admin.notifications.settings')); ?>" class="ap-tab">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'settings', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            إعدادات Firebase و Web Push
        </a>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px;">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'bell', 'size' => 24], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div>
                    <div class="text-muted small fw-medium">إجمالي الإشعارات</div>
                    <div class="fs-4 fw-bold text-dark"><?php echo e($stats['total_sent']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width: 48px; height: 48px;">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'users', 'size' => 24], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div>
                    <div class="text-muted small fw-medium">المشتركون (رموز الأجهزة)</div>
                    <div class="fs-4 fw-bold text-dark"><?php echo e($stats['total_devices']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center <?php echo e($stats['is_firebase_enabled'] ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'); ?>" style="width: 48px; height: 48px;">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'plug', 'size' => 24], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div>
                    <div class="text-muted small fw-medium">خدمة Firebase</div>
                    <div class="fw-bold <?php echo e($stats['is_firebase_enabled'] ? 'text-success' : 'text-danger'); ?>">
                        <?php echo e($stats['is_firebase_enabled'] ? 'مفعلة ونشطة' : 'معطلة'); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center <?php echo e($stats['has_server_key'] ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning'); ?>" style="width: 48px; height: 48px;">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'shield', 'size' => 24], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div>
                    <div class="text-muted small fw-medium">مفتاح السيرفر (FCM)</div>
                    <div class="fw-bold <?php echo e($stats['has_server_key'] ? 'text-success' : 'text-warning'); ?>">
                        <?php echo e($stats['has_server_key'] ? 'متصل وجاهز' : 'غير مدخل بعد'); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="ap-card mb-4">
        <div class="ap-card__body py-3">
            <form method="GET" action="<?php echo e(admin_route('admin.notifications.index')); ?>" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 450px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="ابحث في الإشعارات (العنوان أو النص)..." value="<?php echo e($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'search', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </button>
                    </div>
                    <?php if($search || $selectedTarget): ?>
                        <a href="<?php echo e(admin_route('admin.notifications.index')); ?>" class="btn btn-sm btn-link text-decoration-none">إلغاء التصفية</a>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small fw-bold">الجمهور:</span>
                    <a href="<?php echo e(admin_route('admin.notifications.index', ['target_type' => '', 'search' => $search])); ?>"
                        class="ap-btn <?php echo e(empty($selectedTarget) ? 'ap-btn--primary' : 'ap-btn--ghost'); ?> ap-btn--sm" style="border-radius: 9999px;">
                        الكل
                    </a>
                    <a href="<?php echo e(admin_route('admin.notifications.index', ['target_type' => 'all', 'search' => $search])); ?>"
                        class="ap-btn <?php echo e($selectedTarget === 'all' ? 'ap-btn--primary' : 'ap-btn--ghost'); ?> ap-btn--sm" style="border-radius: 9999px;">
                        عام لجميع المشتركين
                    </a>
                    <a href="<?php echo e(admin_route('admin.notifications.index', ['target_type' => 'user', 'search' => $search])); ?>"
                        class="ap-btn <?php echo e($selectedTarget === 'user' ? 'ap-btn--primary' : 'ap-btn--ghost'); ?> ap-btn--sm" style="border-radius: 9999px;">
                        خاص لمستخدمين
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <div class="ap-card">
        <div class="ap-card__header d-flex align-items-center justify-content-between">
            <h2 class="ap-card__title">سجل الإشعارات</h2>
            <span class="text-muted small">عرض <?php echo e($notifications->count()); ?> من أصل <?php echo e($notifications->total()); ?></span>
        </div>

        <?php if($notifications->isEmpty()): ?>
            <div class="ap-card__body text-center py-5">
                <div class="text-muted mb-3">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'bell', 'size' => 48], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <h3 class="h5 fw-bold text-dark mb-2">لا توجد إشعارات مرسلة حتى الآن</h3>
                <p class="text-muted mb-4">يمكنك إرسال إشعار عام لجميع زوار ومشتركي الموقع، أو إرسال إشعار خاص لمستخدم معين مع صورة ورابط توجيه.</p>
                <a href="<?php echo e(admin_route('admin.notifications.create')); ?>" class="ap-btn ap-btn--primary">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'send', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    إرسال أول إشعار الآن
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="ap-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">الصورة</th>
                            <th>الإشعار</th>
                            <th style="width: 170px;">الجمهور المستهدف</th>
                            <th style="width: 150px;">حالة الإرسال</th>
                            <th style="width: 140px;">تاريخ الإرسال</th>
                            <th style="width: 110px;" class="text-end">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <?php if($item->image_url): ?>
                                        <a href="<?php echo e($item->image_url); ?>" target="_blank" title="عرض الصورة بالحجم الكامل">
                                            <img src="<?php echo e($item->image_url); ?>" alt="صورة الإشعار" class="rounded object-fit-cover shadow-sm border" style="width: 52px; height: 52px;">
                                        </a>
                                    <?php else: ?>
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 52px; height: 52px;">
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'bell', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-1"><?php echo e($item->title); ?></div>
                                    <div class="text-muted small mb-1" style="max-width: 480px; line-height: 1.4;"><?php echo e(Str::limit($item->body, 120)); ?></div>
                                    <?php if($item->action_url): ?>
                                        <a href="<?php echo e($item->action_url); ?>" target="_blank" class="text-primary small text-decoration-none d-inline-flex align-items-center gap-1">
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'external', 'size' => 12], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <?php echo e(Str::limit($item->action_url, 45)); ?>

                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($item->target_type === 'all'): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill">
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'users', 'size' => 13], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            عام للجميع
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 rounded-pill">
                                            مستخدم محدد
                                        </span>
                                        <?php if($item->user): ?>
                                            <div class="fw-medium small text-dark mt-1"><?php echo e($item->user->name); ?></div>
                                            <div class="text-muted" style="font-size: 11px;"><?php echo e($item->user->email); ?></div>
                                        <?php else: ?>
                                            <div class="text-muted small mt-1">مستخدم محذوف (#<?php echo e($item->user_id); ?>)</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($item->sent_count > 0): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            تم البث (<?php echo e($item->sent_count); ?> جهاز)
                                        </span>
                                    <?php elseif($item->fcm_status === 'ready_needs_server_key'): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" title="تم الحفظ بالموقع ولكن يتطلب إدخال مفتاح السيرفر في الإعدادات للبث الخارجي">
                                            محفوظ (ينقصه مفتاح FCM)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            محفوظ في الموقع
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-dark small fw-medium"><?php echo e($item->created_at->format('Y-m-d')); ?></div>
                                    <div class="text-muted" style="font-size: 12px;"><?php echo e($item->created_at->format('H:i')); ?> (<?php echo e($item->created_at->diffForHumans()); ?>)</div>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <form method="POST" action="<?php echo e(admin_route('admin.notifications.destroy', $item->id)); ?>" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإشعار من السجل؟');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="حذف">
                                                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'trash', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <?php if($notifications->hasPages()): ?>
                <div class="ap-card__footer p-3">
                    <?php echo e($notifications->links()); ?>

                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/notifications/index.blade.php ENDPATH**/ ?>