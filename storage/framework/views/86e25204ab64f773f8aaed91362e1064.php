<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('page_title', $title); ?>
<?php $__env->startSection('page_subtitle', $subtitle); ?>

<?php $__env->startSection('page_actions'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('content.manage')): ?>
        <a href="<?php echo e(admin_route('admin.pages.create')); ?>" class="ap-btn ap-btn--gold ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'plus', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> إضافة صفحة
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 260px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" class="ap-input ap-input--sm"
                        placeholder="عنوان الصفحة أو المفتاح أو الرابط…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if(($filters['status'] ?? '') === $status): echo 'selected'; endif; ?>>
                                <?php echo e(status_label($status)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button class="ap-btn ap-btn--sm">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'search', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> فلترة
                </button>
                <a href="<?php echo e(url()->current()); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'file-text', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo e($title); ?>

                <span class="ap-badge"><?php echo e(number_format($pages->total())); ?></span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>العنوان (عربي)</th>
                            <th>المفتاح</th>
                            <th>الرابط</th>
                            <th>العنوان (إنجليزي)</th>
                            <th>الحالة</th>
                            <th>تاريخ النشر</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $arTranslation = $page->translations->firstWhere('locale', 'ar');
                                $enTranslation = $page->translations->firstWhere('locale', 'en');
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-bold"><?php echo e($arTranslation?->title ?? '— بلا عنوان —'); ?></span>
                                </td>
                                <td><code><?php echo e($page->key ?: '—'); ?></code></td>
                                <td class="text-muted" style="font-size: 12px;"><?php echo e($arTranslation?->slug ?: '—'); ?></td>
                                <td><?php echo e($enTranslation?->title ?? '—'); ?></td>
                                <td>
                                    <span class="ap-badge ap-badge--<?php echo e(status_color($page->status)); ?>">
                                        <?php echo e(status_label($page->status)); ?>

                                    </span>
                                </td>
                                <td class="text-muted" style="font-size: 12px;">
                                    <?php echo e($page->published_at ? \Illuminate\Support\Carbon::parse($page->published_at)->format('Y-m-d') : '—'); ?>

                                </td>
                                <td>
                                    <div class="ap-table__actions">
                                        <a href="<?php echo e(admin_route('admin.pages.edit', $page)); ?>"
                                            class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'edit', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </a>
                                        <form method="post" action="<?php echo e(admin_route('admin.pages.destroy', $page)); ?>"
                                            data-confirm="حذف هذه الصفحة؟ يمكن استرجاعها من قاعدة البيانات فقط.">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('delete'); ?>
                                            <button class="ap-btn ap-btn--danger ap-btn--sm" title="حذف">
                                                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'trash', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="ap-empty">
                                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'file-text', 'size' => 42], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <p>لا توجد صفحات مطابقة للفلترة الحالية.</p>
                                        <a href="<?php echo e(admin_route('admin.pages.create')); ?>" class="ap-btn ap-btn--gold ap-btn--sm">
                                            إضافة أول صفحة
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($pages->hasPages()): ?>
            <div class="ap-pagination"><?php echo e($pages->links()); ?></div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/pages/index.blade.php ENDPATH**/ ?>