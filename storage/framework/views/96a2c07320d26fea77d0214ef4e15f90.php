<?php $__env->startSection('title', $title ?? 'العقارات'); ?>
<?php $__env->startSection('page_title', $title ?? 'العقارات'); ?>
<?php $__env->startSection('page_subtitle', $subtitle ?? 'إدارة كاملة لكل العقارات مع الفلترة والإسناد والاعتماد'); ?>

<?php $__env->startSection('page_actions'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.create')): ?>
        <a href="<?php echo e(admin_route('admin.properties.create')); ?>" class="ap-btn ap-btn--gold ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'plus', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> إضافة عقار
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 220px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" class="ap-input ap-input--sm"
                        placeholder="كود العقار أو الاسم…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status->value); ?>" <?php if(($filters['status'] ?? '') === $status->value): echo 'selected'; endif; ?>>
                                <?php echo e(status_label($status)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">الغرض</label>
                    <select name="purpose" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="sale" <?php if(($filters['purpose'] ?? '') === 'sale'): echo 'selected'; endif; ?>>بيع</option>
                        <option value="rent" <?php if(($filters['purpose'] ?? '') === 'rent'): echo 'selected'; endif; ?>>إيجار</option>
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">التصنيف</label>
                    <select name="category_id" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>" <?php if((string) ($filters['category_id'] ?? '') === (string) $category->id): echo 'selected'; endif; ?>>
                                <?php echo e($category->translation('ar')?->name ?? $category->key); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">الإسناد</label>
                    <select name="assignment" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="assigned" <?php if(($filters['assignment'] ?? '') === 'assigned'): echo 'selected'; endif; ?>>مُسند</option>
                        <option value="unassigned" <?php if(($filters['assignment'] ?? '') === 'unassigned'): echo 'selected'; endif; ?>>غير مُسند</option>
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">الترتيب</label>
                    <select name="sort" class="ap-select ap-select--sm">
                        <option value="">الأحدث</option>
                        <option value="views" <?php if(($filters['sort'] ?? '') === 'views'): echo 'selected'; endif; ?>>الأكثر مشاهدة</option>
                        <option value="price_desc" <?php if(($filters['sort'] ?? '') === 'price_desc'): echo 'selected'; endif; ?>>الأعلى سعراً</option>
                        <option value="price_asc" <?php if(($filters['sort'] ?? '') === 'price_asc'): echo 'selected'; endif; ?>>الأقل سعراً</option>
                    </select>
                </div>
                <button class="ap-btn ap-btn--sm">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'search', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> فلترة
                </button>
                <a href="<?php echo e(url()->current()); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    <?php if(($filters['status'] ?? null) === null && !isset($subtitle)): ?>
        <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
            <div class="ap-stat">
                <div class="ap-stat__icon"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                <div>
                    <div class="ap-stat__label">إجمالي النتائج</div>
                    <div class="ap-stat__value"><?php echo e(number_format($properties->total())); ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo e(admin_route('admin.properties.bulk')); ?>" id="bulkForm">
        <?php echo csrf_field(); ?>
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php echo e($title ?? 'العقارات'); ?>

                    <span class="ap-badge"><?php echo e(number_format($properties->total())); ?></span>
                </h2>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <select name="action" class="ap-select ap-select--sm" style="width: auto;">
                        <option value="">إجراء جماعي…</option>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.approve')): ?>
                            <option value="approve">اعتماد ونشر</option>
                            <option value="reject">رفض</option>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.feature')): ?>
                            <option value="feature">تمييز</option>
                            <option value="unfeature">إلغاء التمييز</option>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.assign')): ?>
                            <option value="assign">إسناد إلى…</option>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.delete')): ?>
                            <option value="delete">حذف</option>
                        <?php endif; ?>
                    </select>
                    <select name="value" class="ap-select ap-select--sm" style="width: auto;">
                        <option value="">— اختر المستخدم عند الإسناد —</option>
                        <?php $__currentLoopData = $assignableUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($assignee->id); ?>"><?php echo e($assignee->name); ?> (<?php echo e(user_role_label($assignee->primary_role_slug)); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button class="ap-btn ap-btn--sm ap-btn--ghost" data-confirm="تنفيذ الإجراء الجماعي على العناصر المحددة؟">تنفيذ</button>
                </div>
            </div>

            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead>
                            <tr>
                                <th style="width: 34px;"><input type="checkbox" data-check-all></th>
                                <th>الصورة</th>
                                <th>الكود</th>
                                <th>العقار</th>
                                <th>النوع</th>
                                <th>السعر</th>
                                <th>الحالة</th>
                                <th>المسؤول / السمسار</th>
                                <th>المشاهدات</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="<?php echo e($property->id); ?>" data-check-item></td>
                                    <td>
                                        <img src="<?php echo e($property->primaryImageUrl('ar')); ?>" alt="" class="ap-table__thumb"
                                            loading="lazy">
                                    </td>
                                    <td><code><?php echo e($property->reference_code); ?></code></td>
                                    <td>
                                        <a href="<?php echo e(admin_route('admin.properties.show', $property)); ?>" class="fw-bold">
                                            <?php echo e(\Illuminate\Support\Str::limit($property->displayName('ar'), 42)); ?>

                                        </a>
                                        <div class="text-muted" style="font-size: 11.5px;">
                                            <?php echo e($property->location?->translation('ar')?->name ?? '—'); ?>

                                            <?php if($property->is_featured): ?>
                                                <span class="ap-badge ap-badge--gold">مميز</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ap-badge"><?php echo e($property->purpose === 'rent' ? 'إيجار' : 'بيع'); ?></span>
                                        <div class="text-muted" style="font-size: 11.5px;"><?php echo e($property->category?->translation('ar')?->name ?? '—'); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo e(money_eg($property->price)); ?></div>
                                        <div style="display: flex; align-items: center; gap: 4px; margin-top: 4px; flex-wrap: wrap;">
                                            <?php if($property->price_type === 'negotiable'): ?>
                                                <span class="ap-badge ap-badge--info" style="font-size: 10px; padding: 1px 6px;">تفاوض</span>
                                            <?php elseif($property->price_type === 'contact'): ?>
                                                <span class="ap-badge ap-badge--warning" style="font-size: 10px; padding: 1px 6px;">عند التواصل</span>
                                            <?php else: ?>
                                                <span class="ap-badge ap-badge--secondary" style="font-size: 10px; padding: 1px 6px;">محدد</span>
                                            <?php endif; ?>
                                            <?php if($property->price_notes): ?>
                                                <span title="ملاحظات تفاوض سرية: <?php echo e(Str::limit($property->price_notes, 80)); ?>" style="cursor: help; color: #d97706; font-size: 12px; display: inline-flex; align-items: center;" aria-label="ملاحظات تفاوض سرية">
                                                    🔒
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ap-badge ap-badge--<?php echo e(status_color($property->status)); ?>">
                                            <?php echo e(status_label($property->status)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 12.5px;"><?php echo e($property->manager?->name ?? $property->owner?->name ?? '— غير مُسند —'); ?></div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <?php echo e($property->manager ? user_role_label($property->manager->primary_role_slug) : ''); ?>

                                        </div>
                                    </td>
                                    <td><?php echo e(number_format($property->views_count)); ?></td>
                                    <td>
                                        <div class="ap-table__actions">
                                            <a href="<?php echo e(admin_route('admin.properties.show', $property)); ?>" class="ap-btn ap-btn--ghost ap-btn--sm" title="عرض">
                                                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'eye', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </a>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.update-any')): ?>
                                                <a href="<?php echo e(admin_route('admin.properties.edit', $property)); ?>" class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'edit', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.stats')): ?>
                                                <a href="<?php echo e(admin_route('admin.properties.stats', $property)); ?>" class="ap-btn ap-btn--ghost ap-btn--sm" title="إحصائيات">
                                                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 15], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10">
                                        <div class="ap-empty">
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 42], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <p>لا توجد عقارات مطابقة للفلترة الحالية.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($properties->hasPages()): ?>
                <div class="ap-pagination"><?php echo e($properties->links()); ?></div>
            <?php endif; ?>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (function() {
            var all = document.querySelector('[data-check-all]');
            if (all) {
                all.addEventListener('change', function() {
                    document.querySelectorAll('[data-check-item]').forEach(function(box) {
                        box.checked = all.checked;
                    });
                });
            }
            var form = document.getElementById('bulkForm');
            if (form) {
                form.addEventListener('submit', function(event) {
                    var action = form.querySelector('[name="action"]').value;
                    if (!action) {
                        event.preventDefault();
                        alert('اختر إجراءً جماعياً أولاً.');
                        return;
                    }
                    if (!confirm('تنفيذ الإجراء "' + action + '" على العقارات المحددة؟')) {
                        event.preventDefault();
                    }
                });
            }
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/properties/index.blade.php ENDPATH**/ ?>