<?php $__env->startSection('title', 'الوظائف وفرص العمل'); ?>
<?php $__env->startSection('page_title', 'الوظائف وفرص العمل'); ?>
<?php $__env->startSection('page_subtitle', 'إدارة الوظائف المتاحة واستقبال طلبات التوظيف بالمنصة'); ?>

<?php $__env->startSection('page_actions'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('content.manage')): ?>
        <a href="<?php echo e(admin_route('admin.careers.create')); ?>" class="ap-btn ap-btn--gold ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'plus', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> إضافة وظيفة جديدة
        </a>
        <a href="<?php echo e(admin_route('admin.careers.applications')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> طلبات التقديم (<?php echo e($stats['applications']); ?>)
        </a>
    <?php endif; ?>
    <a href="<?php echo e(route('careers.index', ['locale' => 'ar'])); ?>" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm">
        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'external', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> صفحة الوظائف بالموقع
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="ap-grid ap-grid--4" style="margin-bottom: 20px;">
        <div class="ap-stat">
            <div class="ap-stat__icon"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">إجمالي الوظائف</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['total'])); ?></div>
                <div class="ap-stat__hint">جميع الفرص المسجلة</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'check', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">الوظائف المنشورة</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['published'])); ?></div>
                <div class="ap-stat__hint">تظهر للباحثين عن عمل بالموقع</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'file-text', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">المسودات</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['draft'])); ?></div>
                <div class="ap-stat__hint">وظائف قيد الإعداد أو مغلقة</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'users', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">طلبات التقديم</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['applications'])); ?></div>
                <div class="ap-stat__hint">سير ذاتية واستمارات تقديم</div>
            </div>
        </div>
    </div>

    
    <div class="ap-card" style="margin-bottom: 20px;">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 240px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="<?php echo e(request('q')); ?>" class="ap-input ap-input--sm" placeholder="المسمى الوظيفي، الكلمات المفتاحية…">
                </div>

                <div class="ap-field">
                    <label class="ap-label">القسم / الإدارة</label>
                    <select name="department" class="ap-select ap-select--sm">
                        <option value="">كل الأقسام</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($dept); ?>" <?php if(request('department') === $dept): echo 'selected'; endif; ?>><?php echo e($dept); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="published" <?php if(request('status') === 'published'): echo 'selected'; endif; ?>>منشور فقط</option>
                        <option value="draft" <?php if(request('status') === 'draft'): echo 'selected'; endif; ?>>مسودة</option>
                    </select>
                </div>

                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="<?php echo e(admin_route('admin.careers.index')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                قائمة الوظائف المتاحة
                <span class="ap-badge"><?php echo e(number_format($careers->total())); ?></span>
            </h2>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>المسمى الوظيفي</th>
                            <th>القسم والإدارة</th>
                            <th>الموقع ونوع الدوام</th>
                            <th>نطاق الراتب</th>
                            <th>المستوى</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $careers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $career): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $title = $career->title;
                                $slug = $career->translation('ar')?->slug ?: $career->translation('en')?->slug;
                            ?>
                            <tr>
                                <td>
                                    <div>
                                        <a href="<?php echo e(admin_route('admin.careers.edit', $career)); ?>" class="fw-bold"><?php echo e($title); ?></a>
                                        <div class="text-muted" style="font-size: 11px;"><?php echo e(Str::limit($career->translation()?->summary, 60)); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--info"><?php echo e($career->department ?: 'عام'); ?></span>
                                </td>
                                <td>
                                    <div style="font-size: 13px;"><?php echo e($career->location ?: 'بني سويف'); ?></div>
                                    <div class="text-muted" style="font-size: 11px;"><?php echo e($career->employment_type ?: 'دوام كامل'); ?></div>
                                </td>
                                <td>
                                    <?php if($career->salary_min || $career->salary_max): ?>
                                        <span class="ap-badge ap-badge--gold" style="font-size: 11px;">
                                            <?php echo e(number_format($career->salary_min)); ?> - <?php echo e(number_format($career->salary_max)); ?> ج.م
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 12px;">يحدد في المقابلة</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size: 12px;"><?php echo e($career->experience_level ?: 'متوسط'); ?></span>
                                </td>
                                <td>
                                    <form method="post" action="<?php echo e(admin_route('admin.careers.status', $career)); ?>" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="ap-btn ap-btn--sm <?php echo e($career->status === 'published' ? 'ap-btn--ghost' : 'ap-btn--danger'); ?>" style="padding: 2px 8px; font-size: 11px;">
                                            <span class="ap-badge ap-badge--<?php echo e($career->status === 'published' ? 'success' : 'secondary'); ?>" style="padding:0; background:transparent;">
                                                <?php echo e($career->status === 'published' ? 'منشور' : 'مسودة'); ?>

                                            </span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="<?php echo e(admin_route('admin.careers.edit', $career)); ?>" class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'edit', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </a>
                                        <?php if($slug): ?>
                                            <a href="<?php echo e(route('careers.show', ['locale' => 'ar', 'slug' => $slug])); ?>" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm" title="عرض في الموقع">
                                                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'external', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </a>
                                        <?php endif; ?>
                                        <form method="post" action="<?php echo e(admin_route('admin.careers.destroy', $career)); ?>" onsubmit="return confirm('هل أنت متأكد من حذف هذه الوظيفة؟');" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('delete'); ?>
                                            <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm" title="حذف" style="padding: 4px 6px;">
                                                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'trash', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    لا توجد وظائف مسجلة حالياً.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($careers->hasPages()): ?>
                <div class="p-3 border-top">
                    <?php echo e($careers->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/careers/index.blade.php ENDPATH**/ ?>