<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('page_title', $title); ?>
<?php $__env->startSection('page_subtitle', $subtitle ?? 'إدارة المشروعات العقارية: الأكواد، المواقع، أعداد الوحدات وحالة النشر'); ?>

<?php $__env->startSection('page_actions'); ?>
    <?php if(auth()->user()?->hasPermission('project.manage')): ?>
        <a href="<?php echo e(admin_route('admin.projects.create')); ?>" class="ap-btn ap-btn--gold ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'plus', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> إضافة مشروع
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 240px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" class="ap-input ap-input--sm"
                        placeholder="كود المشروع أو الاسم أو المستثمر…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if(($filters['status'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">التمييز</label>
                    <select name="is_featured" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="1" <?php if(($filters['is_featured'] ?? '') === '1'): echo 'selected'; endif; ?>>المشروعات المميزة فقط</option>
                        <option value="0" <?php if(($filters['is_featured'] ?? '') === '0'): echo 'selected'; endif; ?>>غير المميزة فقط</option>
                    </select>
                </div>
                <button class="ap-btn ap-btn--sm">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'search', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> فلترة
                </button>
                <a href="<?php echo e(url()->current()); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    <div class="ap-grid ap-grid--3" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">إجمالي النتائج</div>
                <div class="ap-stat__value"><?php echo e(number_format($projects->total())); ?></div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'check', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">منشورة في هذه الصفحة</div>
                <div class="ap-stat__value"><?php echo e(number_format($projects->getCollection()->where('status', 'published')->count())); ?></div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'star', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">مميزة في هذه الصفحة</div>
                <div class="ap-stat__value"><?php echo e(number_format($projects->getCollection()->where('is_featured', true)->count())); ?></div>
            </div>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo e($title); ?>

                <span class="ap-badge"><?php echo e(number_format($projects->total())); ?></span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>المشروع</th>
                            <th>المستثمر</th>
                            <th>الموقع</th>
                            <th>الوحدات</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><code><?php echo e($project->reference_code); ?></code></td>
                                <td>
                                    <div class="fw-bold">
                                        <?php echo e(\Illuminate\Support\Str::limit($project->translation('ar')?->name ?? $project->reference_code, 44)); ?>

                                    </div>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        <?php echo e($project->translation('en')?->name ?? '— بدون اسم إنجليزي —'); ?>

                                        <?php if($project->is_featured): ?>
                                            <span class="ap-badge ap-badge--gold">مميز</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo e($project->investor_name ?: '—'); ?></td>
                                <td><?php echo e($project->location?->translation('ar')?->name ?? '—'); ?></td>
                                <td>
                                    <span class="ap-badge ap-badge--info"><?php echo e(number_format($project->units_count)); ?> وحدة</span>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        <?php echo e(number_format((int) $project->flats_count)); ?> شقة ·
                                        <?php echo e(number_format((int) $project->blocks_count)); ?> بلوك
                                    </div>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--<?php echo e(status_color($project->status)); ?>">
                                        <?php echo e($statusLabels[$project->status] ?? status_label($project->status)); ?>

                                    </span>
                                </td>
                                <td><?php echo e($project->created_at?->format('Y/m/d') ?? '—'); ?></td>
                                <td>
                                    <div class="ap-table__actions">
                                        <?php if(auth()->user()?->hasPermission('project.manage')): ?>
                                            <a href="<?php echo e(admin_route('admin.projects.edit', $project)); ?>"
                                                class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'edit', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </a>
                                            <form method="post" action="<?php echo e(admin_route('admin.projects.destroy', $project)); ?>"
                                                data-confirm="حذف المشروع <?php echo e($project->reference_code); ?> نهائياً؟">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('delete'); ?>
                                                <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm" title="حذف">
                                                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'trash', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="ap-empty">
                                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 42], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <p>لا توجد مشروعات مطابقة للفلترة الحالية.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($projects->hasPages()): ?>
            <div class="ap-pagination"><?php echo e($projects->links()); ?></div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/projects/index.blade.php ENDPATH**/ ?>