<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('page_title', $title); ?>
<?php $__env->startSection('page_subtitle', $subtitle ?? ''); ?>

<?php $__env->startSection('page_actions'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.update-any')): ?>
        <a href="<?php echo e(admin_route('admin.properties.edit', $property)); ?>" class="ap-btn ap-btn--gold ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'edit', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> تعديل
        </a>
    <?php endif; ?>
    <a href="<?php echo e(admin_route('admin.properties.stats', $property)); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">
        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> الإحصائيات
    </a>
    <a href="<?php echo e(admin_route('admin.properties.index')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $ar = $property->translation('ar');
        $en = $property->translation('en');
    ?>

    
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'eye', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">المشاهدات</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['views'])); ?></div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'message', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">العملاء المحتملون</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['leads'])); ?></div>
                <div class="ap-stat__hint"><?php echo e($stats['leads_new']); ?> جديد</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'star', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">المفضلة / التقييمات</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['favorites'])); ?> / <?php echo e($stats['reviews']); ?></div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'money', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">سعر المتر</div>
                <div class="ap-stat__value" style="font-size: 18px;"><?php echo e(money_eg($stats['price_per_meter'])); ?></div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--2" style="align-items: start;">
        <div>
            
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'photo', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> الصور</h2>
                    <span class="ap-badge"><?php echo e($property->media->count()); ?></span>
                </div>
                <div class="ap-card__body">
                    <?php if($property->media->isEmpty()): ?>
                        <div class="ap-empty">
                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'photo', 'size' => 38], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <p>لا توجد صور لهذا العقار.</p>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo e($property->primaryImageUrl('ar')); ?>" alt="" style="width: 100%; border-radius: 12px; margin-bottom: 12px; max-height: 320px; object-fit: cover;">
                        <div class="ap-thumb-grid">
                            <?php $__currentLoopData = $property->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="ap-thumb">
                                    <img src="<?php echo e(str_starts_with((string) $media->path, 'http') ? $media->path : asset('storage/' . ltrim($media->path, '/'))); ?>"
                                        alt="" loading="lazy" onerror="this.src='<?php echo e($property->placeholderUrl('ar')); ?>'">
                                    <?php if($media->is_primary): ?>
                                        <span class="ap-badge ap-badge--gold ap-thumb__badge">رئيسية</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <?php
                $adminFloorPlans = $property->getFloorPlans();
            ?>
            <?php if(!empty($adminFloorPlans)): ?>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            <svg style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-left: 6px; color: #b45309;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                <line x1="9" y1="21" x2="9" y2="9"></line>
                            </svg>
                            التخطيط الهندسي للشقة (<?php echo e(count($adminFloorPlans)); ?>)
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <?php $__currentLoopData = $adminFloorPlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: rgba(248, 250, 252, 0.8);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                                        <strong style="color: #1e293b;">📐 <?php echo e($fp['name'] ?? 'مخطط هندسي'); ?></strong>
                                        <div style="display: flex; gap: 10px; font-size: 12px; color: #64748b;">
                                            <?php if(!empty($fp['bedrooms'])): ?> <span><?php echo e($fp['bedrooms']); ?> غرف</span> <?php endif; ?>
                                            <?php if(!empty($fp['bathrooms'])): ?> <span><?php echo e($fp['bathrooms']); ?> حمام</span> <?php endif; ?>
                                            <?php if(!empty($fp['area'])): ?> <span><?php echo e($fp['area']); ?> م²</span> <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if(!empty($fp['description'])): ?>
                                        <p style="font-size: 13px; color: #475569; margin-bottom: 8px;"><?php echo e($fp['description']); ?></p>
                                    <?php endif; ?>
                                    <?php if(!empty($fp['image_url']) || !empty($fp['image'])): ?>
                                        <?php
                                            $adminPlanImg = !empty($fp['image_url']) ? $fp['image_url'] : asset('storage/' . ltrim($fp['image'], '/'));
                                        ?>
                                        <a href="<?php echo e($adminPlanImg); ?>" target="_blank" style="display: block; text-align: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px;">
                                            <img src="<?php echo e($adminPlanImg); ?>" alt="<?php echo e($fp['name'] ?? 'Floor plan'); ?>" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> التفاصيل</h2>
                </div>
                <div class="ap-card__body">
                    <table class="ap-table">
                        <tbody>
                            <tr><th style="width: 45%;">كود العقار</th><td><code><?php echo e($property->reference_code); ?></code></td></tr>
                            <tr><th>الحالة</th><td><span class="ap-badge ap-badge--<?php echo e(status_color($property->status)); ?>"><?php echo e(status_label($property->status)); ?></span></td></tr>
                            <tr><th>الغرض</th><td><?php echo e($property->purpose === 'rent' ? 'إيجار' : 'بيع'); ?></td></tr>
                            <tr>
                                <th>السعر ونوع التسعير</th>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <span class="fw-bold"><?php echo e(money_eg($property->price)); ?></span>
                                        <?php if($property->price_type === 'negotiable'): ?>
                                            <span class="ap-badge ap-badge--info">قابل للتفاوض</span>
                                        <?php elseif($property->price_type === 'contact'): ?>
                                            <span class="ap-badge ap-badge--warning">يتم تحديده من خلال التواصل</span>
                                        <?php else: ?>
                                            <span class="ap-badge ap-badge--secondary">محدد (ثابت)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr><th>التصنيف</th><td><?php echo e($property->category?->translation('ar')?->name ?? '—'); ?></td></tr>
                            <tr><th>الموقع</th><td><?php echo e($property->location?->translation('ar')?->name ?? '—'); ?></td></tr>
                            <tr><th>المشروع</th><td><?php echo e($property->project?->translation('ar')?->name ?? '—'); ?></td></tr>
                            <tr><th>غرف النوم / الحمامات</th><td><?php echo e($property->bedrooms ?? '—'); ?> / <?php echo e($property->bathrooms ?? '—'); ?></td></tr>
                            <tr><th>المساحة</th><td><?php echo e($property->area ? number_format((float) $property->area, 2) . ' ' . $property->area_unit : '—'); ?></td></tr>
                            <tr><th>الدور / الأدوار</th><td><?php echo e($property->floor_number ?? '—'); ?> / <?php echo e($property->floors_count ?? '—'); ?></td></tr>
                            <tr><th>سنة البناء</th><td><?php echo e($property->year_built ?? '—'); ?></td></tr>
                            <tr><th>الإحداثيات</th><td><?php echo e($property->latitude ?? '—'); ?>, <?php echo e($property->longitude ?? '—'); ?></td></tr>
                            <tr><th>مميز</th><td><?php echo $property->is_featured ? '<span class="ap-badge ap-badge--gold">نعم</span>' : 'لا'; ?></td></tr>
                            <tr><th>تاريخ النشر</th><td><?php echo e($property->published_at?->format('Y-m-d H:i') ?? '—'); ?></td></tr>
                            <tr><th>ينتهي في</th><td><?php echo e($property->expires_at?->format('Y-m-d') ?? '—'); ?></td></tr>
                            <tr><th>أُنشئ</th><td><?php echo e($property->created_at?->format('Y-m-d H:i')); ?></td></tr>
                        </tbody>
                    </table>

                    <?php if($property->price_notes): ?>
                        <div class="ap-alert" style="margin-top: 14px; margin-bottom: 0; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 8px; padding: 12px 16px;">
                            <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                <svg style="width: 16px; height: 16px; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <span>ملاحظات سرية خاصة بالسعر والتفاوض (بين الإدارة والعميل):</span>
                            </div>
                            <div style="white-space: pre-line; color: #78350f; font-size: 0.9rem;"><?php echo e($property->price_notes); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if($property->admin_note): ?>
                        <div class="ap-alert ap-alert--warning" style="margin-top: 14px; margin-bottom: 0;">
                            <div><strong>ملاحظات الإدارة:</strong> <?php echo e($property->admin_note); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'users', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> الإسناد والملكية</h2>
                </div>
                <div class="ap-card__body">
                    <table class="ap-table">
                        <tbody>
                            <tr><th style="width: 45%;">المالك / السمسار</th><td><?php echo e($property->owner?->name ?? '—'); ?></td></tr>
                            <tr><th>المسؤول المتابع</th><td><?php echo e($property->manager?->name ?? '—'); ?></td></tr>
                            <tr><th>منشئ العقار</th><td><?php echo e($property->creator?->name ?? '—'); ?></td></tr>
                            <tr><th>حالة الإسناد</th><td><span class="ap-badge ap-badge--<?php echo e($property->assignment_status === 'assigned' ? 'success' : 'warning'); ?>"><?php echo e($property->assignmentLabel()); ?></span></td></tr>
                            <tr><th>آخر إسناد</th><td><?php echo e($property->last_assigned_at?->diffForHumans() ?? '—'); ?></td></tr>
                        </tbody>
                    </table>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.assign')): ?>
                        <form method="post" action="<?php echo e(admin_route('admin.properties.assign', $property)); ?>" class="ap-grid ap-grid--3" style="margin-top: 14px;">
                            <?php echo csrf_field(); ?>
                            <div class="ap-field" style="margin-bottom: 0;">
                                <label class="ap-label">المالك / السمسار</label>
                                <select name="owner_id" class="ap-select ap-select--sm" required>
                                    <?php $__currentLoopData = $assignableUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($assignee->id); ?>" <?php if($property->owner_id === $assignee->id): echo 'selected'; endif; ?>><?php echo e($assignee->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="ap-field" style="margin-bottom: 0;">
                                <label class="ap-label">المسؤول المتابع</label>
                                <select name="managed_by" class="ap-select ap-select--sm">
                                    <option value="">— نفس المالك —</option>
                                    <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($manager->id); ?>" <?php if($property->managed_by === $manager->id): echo 'selected'; endif; ?>><?php echo e($manager->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="ap-field" style="margin-bottom: 0;">
                                <label class="ap-label">&nbsp;</label>
                                <button class="ap-btn ap-btn--sm ap-btn--block">إسناد العقار</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'toggle', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> إجراءات سريعة</h2>
                </div>
                <div class="ap-card__body" style="display: grid !important; grid-template-columns: 1fr 1fr 1fr !important; gap: 8px !important; padding: 12px 10px !important;">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.approve')): ?>
                        <form method="post" action="<?php echo e(admin_route('admin.properties.approve', $property)); ?>" style="margin: 0; min-width: 0; width: 100%;">
                            <?php echo csrf_field(); ?>
                            <button class="ap-btn ap-btn--success" style="width: 100% !important; min-width: 0 !important; font-size: 12px !important; padding: 9px 4px !important; white-space: nowrap !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; font-weight: 700 !important; border-radius: 8px !important;">
                                اعتماد ونشر العقار
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.feature')): ?>
                        <form method="post" action="<?php echo e(admin_route('admin.properties.feature', $property)); ?>" style="margin: 0; min-width: 0; width: 100%;">
                            <?php echo csrf_field(); ?>
                            <button class="ap-btn ap-btn--gold" style="width: 100% !important; min-width: 0 !important; font-size: 12px !important; padding: 9px 4px !important; white-space: nowrap !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; font-weight: 700 !important; border-radius: 8px !important;">
                                <?php echo e($property->is_featured ? 'إلغاء التمييز' : 'تمييز العقار'); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.delete')): ?>
                        <form method="post" action="<?php echo e(admin_route('admin.properties.destroy', $property)); ?>"
                            data-confirm="حذف العقار نهائياً؟" style="margin: 0; min-width: 0; width: 100%;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('delete'); ?>
                            <button class="ap-btn ap-btn--danger" style="width: 100% !important; min-width: 0 !important; font-size: 12px !important; padding: 9px 4px !important; white-space: nowrap !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; font-weight: 700 !important; border-radius: 8px !important;">
                                حذف العقار
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.approve')): ?>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'x', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> رفض العقار</h2>
                    </div>
                    <div class="ap-card__body">
                        <form method="post" action="<?php echo e(admin_route('admin.properties.reject', $property)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="ap-field">
                                <label class="ap-label">سبب الرفض <span>*</span></label>
                                <textarea name="reason" class="ap-textarea" rows="3" required placeholder="وضّح سبب الرفض ليصل للسمسار…"></textarea>
                            </div>
                            <button class="ap-btn ap-btn--danger ap-btn--sm">رفض العقار</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'file-text', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> المحتوى</h2>
                </div>
                <div class="ap-card__body">
                    <h3 style="font-size: 13.5px;">عربي</h3>
                    <p class="fw-bold"><?php echo e($ar?->name ?? '—'); ?></p>
                    <p class="text-muted" style="font-size: 12.5px;"><?php echo e($ar?->short_description ?? ''); ?></p>
                    <div style="font-size: 13px; white-space: pre-line;"><?php echo e(\Illuminate\Support\Str::limit(strip_tags((string) $ar?->description), 600)); ?></div>

                    <?php if($en): ?>
                        <hr style="margin: 14px 0; border: 0; border-top: 1px solid var(--ap-border);">
                        <h3 style="font-size: 13.5px;">English</h3>
                        <p class="fw-bold"><?php echo e($en->name); ?></p>
                        <div style="font-size: 13px; white-space: pre-line;"><?php echo e(\Illuminate\Support\Str::limit(strip_tags((string) $en->description), 400)); ?></div>
                    <?php endif; ?>

                    <?php if($property->amenities->isNotEmpty()): ?>
                        <hr style="margin: 14px 0; border: 0; border-top: 1px solid var(--ap-border);">
                        <h3 style="font-size: 13.5px;">المرافق</h3>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $__currentLoopData = $property->amenities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $amenity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="ap-badge"><?php echo e($amenity->translation('ar')?->name ?? $amenity->key); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'activity', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> سجل تغييرات الحالة</h2>
                </div>
                <div class="ap-card__body ap-card__body--tight">
                    <div class="ap-table-wrap">
                        <table class="ap-table">
                            <thead>
                                <tr><th>من</th><th>إلى</th><th>السبب</th><th>التاريخ</th></tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $property->statusHistory->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e(status_label($history->old_status)); ?></td>
                                        <td><span class="ap-badge ap-badge--<?php echo e(status_color($history->new_status)); ?>"><?php echo e(status_label($history->new_status)); ?></span></td>
                                        <td><?php echo e(\Illuminate\Support\Str::limit($history->reason, 60) ?: '—'); ?></td>
                                        <td><?php echo e($history->created_at?->format('Y-m-d H:i')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="4" class="text-muted text-center">لا يوجد سجل بعد.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/properties/show.blade.php ENDPATH**/ ?>