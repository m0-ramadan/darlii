<?php $__env->startSection('title', 'الشركات والوكلاء العقاريين'); ?>
<?php $__env->startSection('page_title', 'الشركات والوكلاء العقاريين'); ?>
<?php $__env->startSection('page_subtitle', 'إدارة وتوثيق شركات ووكلاء العقارات المعتمدين المرتبطين بالمنصة'); ?>

<?php $__env->startSection('page_actions'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.create')): ?>
        <a href="<?php echo e(admin_route('admin.agents.create')); ?>" class="ap-btn ap-btn--gold ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'plus', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> إضافة وكيل / شركة
        </a>
    <?php endif; ?>
    <a href="<?php echo e(route('agents.index', ['locale' => 'ar'])); ?>" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm">
        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'external', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> صفحة الوكلاء بالموقع
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="ap-grid ap-grid--4" style="margin-bottom: 20px;">
        <div class="ap-stat">
            <div class="ap-stat__icon"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">إجمالي الوكلاء والشركات</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['total'])); ?></div>
                <div class="ap-stat__hint">يشمل الشركات والوكلاء المسجلين</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'star', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">الوكلاء المعتمدون (موثق)</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['verified'])); ?></div>
                <div class="ap-stat__hint">يحملون شارة الاعتماد والتوثيق</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'check', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">الحسابات النشطة</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['active'])); ?></div>
                <div class="ap-stat__hint">تظهر في دليل الوكلاء بالموقع</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            <div style="flex: 1;">
                <div class="ap-stat__label">الشركات والوكالات</div>
                <div class="ap-stat__value"><?php echo e(number_format($stats['agencies'])); ?></div>
                <div class="ap-stat__hint">الكيانات والوكالات العقارية</div>
            </div>
        </div>
    </div>

    
    <div class="ap-card" style="margin-bottom: 20px;">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 220px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="<?php echo e(request('q')); ?>" class="ap-input ap-input--sm" placeholder="الاسم، الشركة، البريد أو الهاتف…">
                </div>

                <div class="ap-field">
                    <label class="ap-label">الشركة / الوكالة</label>
                    <select name="agency_id" class="ap-select ap-select--sm">
                        <option value="">كل الشركات</option>
                        <?php $__currentLoopData = $agencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($agency->id); ?>" <?php if(request('agency_id') == $agency->id): echo 'selected'; endif; ?>><?php echo e($agency->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="ap-field">
                    <label class="ap-label">التوثيق والاعتماد</label>
                    <select name="verified" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="1" <?php if(request('verified') === '1'): echo 'selected'; endif; ?>>معتمد فقط</option>
                        <option value="0" <?php if(request('verified') === '0'): echo 'selected'; endif; ?>>غير معتمد</option>
                    </select>
                </div>

                <div class="ap-field">
                    <label class="ap-label">نوع المسؤول</label>
                    <select name="agent_type" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="individual" <?php if(request('agent_type') === 'individual'): echo 'selected'; endif; ?>>شخص / مسؤول فردي</option>
                        <option value="office" <?php if(request('agent_type') === 'office'): echo 'selected'; endif; ?>>مكتب عقارات / شركة</option>
                        <option value="broker" <?php if(request('agent_type') === 'broker'): echo 'selected'; endif; ?>>سمسار عادي</option>
                    </select>
                </div>

                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>نشط</option>
                        <option value="inactive" <?php if(request('status') === 'inactive'): echo 'selected'; endif; ?>>غير نشط</option>
                    </select>
                </div>

                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="<?php echo e(admin_route('admin.agents.index')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                دليل الوكلاء والشركات
                <span class="ap-badge"><?php echo e(number_format($agents->total())); ?></span>
            </h2>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الوكيل / المسؤول</th>
                            <th>نوع المسؤول والكيان</th>
                            <th>المسمى والترخيص</th>
                            <th>بنود التعامل والنسب</th>
                            <th>بيانات التواصل</th>
                            <th>العقارات</th>
                            <th>الاعتماد</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $name = $agent->display_name;
                                $avatarNum = (($agent->id - 1) % 11) + 1;
                                $avatarUrl = $agent->avatar_path ? asset($agent->avatar_path) : asset("storage/avatars/{$avatarNum}.jpg");
                                $propCount = $agent->user?->properties()->count() ?? 0;
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo e($avatarUrl); ?>" alt="<?php echo e($name); ?>" class="ap-avatar ap-avatar--sm" style="object-fit: cover;" onerror="this.onerror=null;this.src='<?php echo e(asset('themes/homzen/images/darli-placeholder.png')); ?>';">
                                        <div>
                                            <a href="<?php echo e(admin_route('admin.agents.edit', $agent)); ?>" class="fw-bold"><?php echo e($name); ?></a>
                                            <div class="text-muted" dir="ltr" style="font-size: 11px;"><?php echo e($agent->slug); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="ap-badge <?php echo e($agent->agent_type_badge_class); ?>" style="font-size: 11px;">
                                            <?php echo e($agent->agent_type_label); ?>

                                        </span>
                                    </div>
                                    <?php if($agent->agency): ?>
                                        <div style="margin-top: 4px;"><span class="text-muted" style="font-size: 11px;">تابع لـ: <?php echo e($agent->agency->name); ?></span></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size: 13px;"><?php echo e($agent->job_title ?: 'مستشار عقاري'); ?></div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <?php if($agent->license_number): ?> ترخيص: <?php echo e($agent->license_number); ?> · <?php endif; ?>
                                        خبرة: <?php echo e($agent->years_experience); ?> سنة
                                    </div>
                                </td>
                                <td>
                                    <?php if($agent->contract_terms): ?>
                                        <div title="<?php echo e($agent->contract_terms); ?>" style="max-width: 200px; font-size: 11px; line-height: 1.4; color: #b45309; background: rgba(245,158,11,0.08); padding: 4px 8px; border-radius: 6px; border: 1px dashed rgba(245,158,11,0.3); cursor: help;">
                                            <span class="fw-bold">📜 بنود / نسب:</span>
                                            <div><?php echo e(Str::limit($agent->contract_terms, 55)); ?></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 11.5px;">— لا توجد بنود —</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 12px;">
                                        <?php if($agent->user?->phone): ?>
                                            <div><span dir="ltr"><?php echo e($agent->user->phone); ?></span></div>
                                        <?php endif; ?>
                                        <?php if($agent->user?->email): ?>
                                            <div class="text-muted" dir="ltr" style="font-size: 11px;"><?php echo e($agent->user->email); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--<?php echo e($propCount > 0 ? 'gold' : 'secondary'); ?>">
                                        <?php echo e($propCount); ?> عقار
                                    </span>
                                </td>
                                <td>
                                    <form method="post" action="<?php echo e(admin_route('admin.agents.verify', $agent)); ?>" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="ap-btn ap-btn--sm <?php echo e($agent->isVerified() ? 'ap-btn--success' : 'ap-btn--ghost'); ?>" title="<?php echo e($agent->isVerified() ? 'إلغاء الاعتماد' : 'توثيق واعتماد'); ?>" style="padding: 2px 8px; font-size: 11px;">
                                            <?php if($agent->isVerified()): ?>
                                                ✓ معتمد
                                            <?php else: ?>
                                                توثيق
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" action="<?php echo e(admin_route('admin.agents.status', $agent)); ?>" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="ap-btn ap-btn--sm <?php echo e($agent->status === 'active' ? 'ap-btn--ghost' : 'ap-btn--danger'); ?>" style="padding: 2px 8px; font-size: 11px;">
                                            <span class="ap-badge ap-badge--<?php echo e($agent->status === 'active' ? 'success' : 'danger'); ?>" style="padding:0; background:transparent;">
                                                <?php echo e($agent->status === 'active' ? 'نشط' : 'معطل'); ?>

                                            </span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="<?php echo e(admin_route('admin.agents.edit', $agent)); ?>" class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'edit', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </a>
                                        <a href="<?php echo e(route('agents.show', ['locale' => 'ar', 'slug' => $agent->slug])); ?>" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm" title="عرض في الموقع">
                                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'external', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </a>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.delete')): ?>
                                            <form method="post" action="<?php echo e(admin_route('admin.agents.destroy', $agent)); ?>" onsubmit="return confirm('هل أنت متأكد من حذف هذا الوكيل نهائياً؟');" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('delete'); ?>
                                                <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm" title="حذف" style="padding: 4px 6px;">
                                                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'trash', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    لا يوجد وكلاء يطابقون خيارات البحث.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($agents->hasPages()): ?>
                <div class="p-3 border-top">
                    <?php echo e($agents->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/agents/index.blade.php ENDPATH**/ ?>