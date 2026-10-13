<?php $__env->startSection('title', 'طلبات التقديم للوظائف'); ?>
<?php $__env->startSection('page_title', 'طلبات التقديم للوظائف'); ?>
<?php $__env->startSection('page_subtitle', 'قائمة السير الذاتية واستمارات المتقدمين لفرص العمل'); ?>

<?php $__env->startSection('page_actions'); ?>
    <a href="<?php echo e(admin_route('admin.careers.index')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">
        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> قائمة الوظائف
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                الطلبات المستلمة
                <span class="ap-badge"><?php echo e(number_format($applications->total())); ?></span>
            </h2>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>المتقدم</th>
                            <th>بيانات الاتصال</th>
                            <th>تفاصيل ومؤهلات المتقدم</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo e($app->name); ?></div>
                                    <span class="ap-badge ap-badge--info" style="font-size: 11px;">وظائف دارلي</span>
                                </td>
                                <td>
                                    <div><a href="tel:<?php echo e($app->phone); ?>" dir="ltr"><?php echo e($app->phone); ?></a></div>
                                    <div class="text-muted" style="font-size: 11px;" dir="ltr"><?php echo e($app->email); ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; max-width: 480px; white-space: pre-wrap;"><?php echo e($app->notes ?: $app->message); ?></div>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size: 12px;"><?php echo e($app->created_at?->diffForHumans()); ?></span>
                                </td>
                                <td>
                                    <a href="<?php echo e(admin_route('admin.leads.show', $app)); ?>" class="ap-btn ap-btn--ghost ap-btn--sm" title="تفاصيل">
                                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'eye', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    لا توجد طلبات تقديم حالياً.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($applications->hasPages()): ?>
                <div class="p-3 border-top">
                    <?php echo e($applications->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/careers/applications.blade.php ENDPATH**/ ?>