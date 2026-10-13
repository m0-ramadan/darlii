<?php $__env->startSection('title', 'عقاراتي'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container page-section" style="padding-top: 40px; padding-bottom: 60px;">
        <?php if(session('success')): ?>
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert"
                style="border-radius: 12px; background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
                <div>
                    <strong>✓ تم بنجاح:</strong> <?php echo e(session('success')); ?>

                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1">عقاراتي المسجلة</h1>
                <p class="text-muted mb-0" style="font-size: 14px;">متابعة حالة العقارات الخاصة بك والمضافة للمنصة</p>
            </div>
            <a class="btn btn-primary" href="<?php echo e(route('dashboard.properties.create', ['locale' => app()->getLocale()])); ?>"
                style="background: #C49A45; border-color: #C49A45; font-weight: 600; padding: 10px 22px; border-radius: 8px;">
                + إضافة عقار جديد
            </a>
        </div>

        
        <div class="p-3 mb-4"
            style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; font-size: 13.5px; color: #92400E;">
            ℹ️ <strong>ملاحظة للمستخدمين:</strong> عند إضافة أي عقار جديد، يتم حفظه فوراً بحالة <strong>«تحت
                المراجعة»</strong> لمطابقة بياناته من قِبل إدارة المنصة، ولا يظهر في نتائج البحث والصفحات العامة للموقع إلا
            بعد اعتماد الإدارة وتأكيده لضمان جودة العروض.
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                    <thead style="background: #F8FAFC;">
                        <tr>
                            <th style="padding: 14px 18px;">كود العقار</th>
                            <th>اسم العقار</th>
                            <th>السعر</th>
                            <th>الحالة بالموقع</th>
                            <th class="text-end" style="padding-left: 18px;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $st = $p->status?->value ?? (string) $p->status;
                            ?>
                            <tr>
                                <td style="padding: 14px 18px;">
                                    <code><?php echo e($p->reference_code); ?></code>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo e($p->translation()?->name ?: 'عقار بدون اسم'); ?></div>
                                    <div class="text-muted" style="font-size: 12px;">
                                        <?php echo e($p->location?->translation()?->name ?? '—'); ?></div>
                                </td>
                                <td>
                                    <?php if($p->isPriceOnContact()): ?>
                                        <span class="badge text-bg-secondary">عند التواصل</span>
                                    <?php else: ?>
                                        <span class="fw-bold"><?php echo e(money_eg($p->price)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($st === 'pending_review'): ?>
                                        <span class="badge"
                                            style="background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            ⏳ تحت المراجعة
                                        </span>
                                    <?php elseif($st === 'published'): ?>
                                        <span class="badge"
                                            style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            ✓ معتمد ومنشور بالموقع
                                        </span>
                                    <?php elseif($st === 'sold'): ?>
                                        <span class="badge"
                                            style="background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            تم البيع
                                        </span>
                                    <?php elseif($st === 'rejected'): ?>
                                        <span class="badge"
                                            style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            ✕ مرفوض
                                        </span>
                                    <?php else: ?>
                                        <span class="badge"
                                            style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            <?php echo e($st); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end" style="padding-left: 18px;">
                                    <?php if($st === 'draft' || $st === 'rejected'): ?>
                                        <form method="post"
                                            action="<?php echo e(route('dashboard.properties.submit', ['locale' => app()->getLocale(), 'property' => $p])); ?>"
                                            style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-sm btn-outline-primary"
                                                style="border-radius: 6px; font-size: 12px;">إرسال للمراجعة</button>
                                        </form>
                                    <?php elseif($st === 'pending_review'): ?>
                                        <span class="text-muted" style="font-size: 12px;">بانتظار موافقة الإدارة</span>
                                    <?php elseif($st === 'published'): ?>
                                        <a href="<?php echo e(route('properties.show', ['locale' => app()->getLocale(), 'slug' => $p->translation()?->slug ?? $p->id])); ?>"
                                            class="btn btn-sm btn-outline-success" target="_blank"
                                            style="border-radius: 6px; font-size: 12px;">
                                            عرض بالموقع ↗
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    لا توجد عقارات مسجلة لديك حتى الآن. اضغط على «+ إضافة عقار جديد» للبدء.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($properties->hasPages()): ?>
            <div class="mt-4">
                <?php echo e($properties->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard/properties/index.blade.php ENDPATH**/ ?>