<?php $__env->startSection('title', 'لوحة التحكم'); ?>
<?php $__env->startSection('page_title', 'مرحباً ' . auth()->user()->name); ?>
<?php $__env->startSection('page_subtitle', 'نظرة شاملة على أداء المنصة العقارية — ' . now()->translatedFormat('l j F Y')); ?>

<?php $__env->startSection('page_actions'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.create')): ?>
        <a href="<?php echo e(admin_route('admin.properties.create')); ?>" class="ap-btn ap-btn--gold ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'plus', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> عقار جديد
        </a>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stats.view')): ?>
        <a href="<?php echo e(admin_route('admin.stats.index')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> التقارير
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $maxTrend = max(1, max(array_merge($trend['properties'], $trend['leads'], $trend['users'])));
        $statusColors = ['#0F3D2E', '#C8A45C', '#1d6fa5', '#b98900', '#c0392b', '#55636b'];
        $statusTotal = max(1, array_sum($statusDistribution['values']));
    ?>

    
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">إجمالي العقارات</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['properties_total'])); ?></div>
                <div class="ap-stat__hint">
                    <?php echo e($overview['properties_published']); ?> منشور · <?php echo e($overview['properties_pending']); ?> بانتظار المراجعة
                    <?php if($propertyComparison['change'] != 0): ?>
                        <span class="ap-stat__trend ap-stat__trend--<?php echo e($propertyComparison['change'] > 0 ? 'up' : 'down'); ?>">
                            (<?php echo e($propertyComparison['change'] > 0 ? '+' : ''); ?><?php echo e($propertyComparison['change']); ?>% هذا الشهر)
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'money', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">إجمالي قيمة العقارات</div>
                <div class="ap-stat__value" style="font-size: 19px;"><?php echo e(money_eg($overview['properties_value'])); ?></div>
                <div class="ap-stat__hint">متوسط السعر <?php echo e(money_eg($overview['properties_avg_price'])); ?></div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'eye', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">مشاهدات العقارات</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['properties_views'])); ?></div>
                <div class="ap-stat__hint"><?php echo e($overview['properties_featured']); ?> عقار مميز</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'message', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">العملاء المحتملون</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['leads_total'])); ?></div>
                <div class="ap-stat__hint"><?php echo e($overview['leads_new']); ?> جديد · <?php echo e($overview['leads_month']); ?> هذا الشهر</div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'check', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">منشور</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['properties_published'])); ?></div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">بانتظار المراجعة</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['properties_pending'])); ?></div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'edit', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">مسودات</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['properties_draft'])); ?></div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--danger"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'x', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">مرفوض / غير مُسند</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['properties_rejected'])); ?> / <?php echo e(number_format($overview['properties_unassigned'])); ?></div>
            </div>
        </div>
    </div>

    
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'users', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">إجمالي المستخدمين</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['users_total'])); ?></div>
                <div class="ap-stat__hint"><?php echo e($overview['users_active']); ?> نشط</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'shield', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">المسؤولون</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['users_managers'])); ?></div>
                <div class="ap-stat__hint"><?php echo e($overview['users_admins']); ?> أدمن</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'activity', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">السماسرة</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['users_agents'])); ?></div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div>
                <div class="ap-stat__label">المواعيد</div>
                <div class="ap-stat__value"><?php echo e(number_format($overview['appointments_total'])); ?></div>
                <div class="ap-stat__hint"><?php echo e($overview['appointments_upcoming']); ?> قادمة</div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--2" style="align-items: start;">
        
        <div class="ap-card" style="grid-column: span 2;">
            <div class="ap-card__head">
                <div>
                    <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> النشاط في آخر 14 يوماً</h2>
                    <p class="ap-card__subtitle">العقارات الجديدة مقابل العملاء المحتملين</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="ap-badge ap-badge--info">عقارات</span>
                    <span class="ap-badge ap-badge--gold">عملاء</span>
                </div>
            </div>
            <div class="ap-card__body">
                <div class="ap-bars">
                    <?php $__currentLoopData = $trend['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="ap-bars__item"
                            title="<?php echo e($label); ?> — عقارات: <?php echo e($trend['properties'][$index]); ?> / عملاء: <?php echo e($trend['leads'][$index]); ?>">
                            <span class="ap-bars__value"><?php echo e($trend['properties'][$index] ?: ''); ?></span>
                            <div class="ap-bars__bar" style="height: <?php echo e(max(3, (int) round(($trend['properties'][$index] / $maxTrend) * 100))); ?>%;"></div>
                            <div class="ap-bars__bar ap-bars__bar--accent" style="height: <?php echo e(max(2, (int) round(($trend['leads'][$index] / $maxTrend) * 100))); ?>%;"></div>
                            <span class="ap-bars__label"><?php echo e($label); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> توزيع حالات العقارات</h2>
            </div>
            <div class="ap-card__body">
                <?php $__empty_1 = true; $__currentLoopData = $statusDistribution['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $value = $statusDistribution['values'][$index]; $percent = round(($value / $statusTotal) * 100); ?>
                    <div style="margin-bottom: 12px;">
                        <div class="d-flex justify-content-between" style="font-size: 12.8px; margin-bottom: 4px;">
                            <span><?php echo e($label); ?></span>
                            <span class="fw-bold"><?php echo e(number_format($value)); ?> (<?php echo e($percent); ?>%)</span>
                        </div>
                        <div class="ap-progress">
                            <div class="ap-progress__bar" style="width: <?php echo e($percent); ?>%; background: <?php echo e($statusColors[$index % count($statusColors)]); ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="ap-empty"><p>لا توجد بيانات.</p></div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'star', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> أفضل السماسرة</h2>
            </div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead><tr><th>السمسار</th><th>عدد العقارات</th><th>الحالة</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $topAgents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?php echo e($agent->avatar_url); ?>" alt="" class="ap-avatar ap-avatar--sm">
                                            <span><?php echo e($agent->name); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo e(number_format($agent->properties_count)); ?></td>
                                    <td><span class="ap-badge ap-badge--<?php echo e($agent->status === 'active' ? 'success' : 'secondary'); ?>"><?php echo e(status_label($agent->status)); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="3" class="text-center text-muted">لا يوجد سماسرة بعد.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> أحدث العقارات</h2>
                <a href="<?php echo e(admin_route('admin.properties.index')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">الكل</a>
            </div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead><tr><th>العقار</th><th>المسؤول</th><th>الحالة</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentProperties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo e(admin_route('admin.properties.show', $property)); ?>" class="fw-bold">
                                            <?php echo e(\Illuminate\Support\Str::limit($property->displayName('ar'), 30)); ?>

                                        </a>
                                        <div class="text-muted" style="font-size: 11px;"><?php echo e(money_eg($property->price)); ?></div>
                                    </td>
                                    <td><?php echo e($property->manager?->name ?? $property->owner?->name ?? '—'); ?></td>
                                    <td><span class="ap-badge ap-badge--<?php echo e(status_color($property->status)); ?>"><?php echo e(status_label($property->status)); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="3" class="text-center text-muted">لا توجد عقارات.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'message', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> أحدث العملاء المحتملين</h2>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('lead.view-all')): ?>
                    <a href="<?php echo e(admin_route('admin.leads.index')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">الكل</a>
                <?php endif; ?>
            </div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead><tr><th>العميل</th><th>العقار</th><th>الحالة</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentLeads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold"><?php echo e($lead->name); ?></span>
                                        <div class="text-muted" dir="ltr" style="font-size: 11px;"><?php echo e($lead->phone ?? $lead->email); ?></div>
                                    </td>
                                    <td><?php echo e(\Illuminate\Support\Str::limit($lead->property?->displayName('ar') ?? '—', 24)); ?></td>
                                    <td><span class="ap-badge ap-badge--<?php echo e(status_color($lead->status)); ?>"><?php echo e(status_label($lead->status)); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="3" class="text-center text-muted">لا يوجد عملاء محتملون.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>