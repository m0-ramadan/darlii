<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
?>

<?php $__env->startSection('title', $isAr ? 'لوحة التحكم الخاصة' : 'Partner Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container page-section" style="padding-top: 35px; padding-bottom: 60px;">
    <?php if(session('success')): ?>
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert"
            style="border-radius: 12px; background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
            <div><strong>✓ تم بنجاح:</strong> <?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    <?php if(session('status_warning') || $isPending): ?>
        <div class="alert alert-warning mb-4 shadow-sm" role="alert"
            style="border-radius: 12px; background: #FFFBEB; border-color: #FDE68A; color: #92400E; padding: 18px 22px;">
            <div class="d-flex align-items-start gap-3">
                <div style="font-size: 26px; line-height: 1;">⏳</div>
                <div>
                    <h5 class="fw-bold mb-1" style="color: #B45309; font-size: 1.05rem;">
                        <?php echo e($isAr ? 'حسابك قيد المراجعة والاعتماد' : 'Account Under Review'); ?>

                    </h5>
                    <p class="mb-0" style="font-size: 0.92rem; line-height: 1.6;">
                        <?php echo e(session('status_warning') ?? ($isAr
                            ? 'مرحباً بك في دارلي! حسابك حالياً قيد المراجعة والاعتماد من قبل إدارة المنصة للتحقق من البيانات والمستندات. بمجرد تفعيل الحساب ستتمكن من إضافة ونشر العقارات والمشاريع الخاصة بك مباشرة.'
                            : 'Welcome to Darli! Your account is currently under review by platform administration. Once approved, you will be able to publish properties and projects.')); ?>

                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="card shadow-sm border-0 mb-4 p-4" style="border-radius: 16px; background: linear-gradient(135deg, #111e33 0%, #1e293b 100%); color: #ffffff;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="<?php echo e($user->avatar_url); ?>" alt="<?php echo e($user->name); ?>" class="rounded-circle shadow" style="width: 64px; height: 64px; object-fit: cover; border: 3px solid rgba(255, 255, 255, 0.2);">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 class="h4 fw-bold mb-0 text-white"><?php echo e($user->name); ?></h2>
                        <?php if($isCompany): ?>
                            <span class="badge" style="background: rgba(196, 154, 69, 0.25); color: #F8E3A6; border: 1px solid rgba(196, 154, 69, 0.5); font-size: 11.5px; padding: 4px 10px; border-radius: 20px;">
                                🏢 <?php echo e($user->agency?->name ?: ($isAr ? 'شركة عقارية' : 'Company')); ?>

                            </span>
                        <?php elseif($isBroker): ?>
                            <span class="badge" style="background: rgba(56, 189, 248, 0.2); color: #BAE6FD; border: 1px solid rgba(56, 189, 248, 0.4); font-size: 11.5px; padding: 4px 10px; border-radius: 20px;">
                                🤝 <?php echo e($isAr ? 'سمسار / وسيط عقاري' : 'Broker'); ?>

                            </span>
                        <?php else: ?>
                            <span class="badge" style="background: rgba(148, 163, 184, 0.2); color: #E2E8F0; font-size: 11.5px; padding: 4px 10px; border-radius: 20px;">
                                👤 <?php echo e($isAr ? 'عميل' : 'Customer'); ?>

                            </span>
                        <?php endif; ?>

                        <?php if($isPending): ?>
                            <span class="badge bg-warning text-dark" style="font-size: 11.5px; padding: 4px 10px; border-radius: 20px;">
                                ⏳ <?php echo e($isAr ? 'معلق (بانتظار التفعيل)' : 'Pending Review'); ?>

                            </span>
                        <?php else: ?>
                            <span class="badge bg-success" style="font-size: 11.5px; padding: 4px 10px; border-radius: 20px;">
                                ✓ <?php echo e($isAr ? 'حساب مفعّل ونشط' : 'Active Account'); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="mb-0 text-light opacity-75 mt-1" style="font-size: 13.5px;">
                        <?php echo e($isAr ? 'متابعة أداء العقارات والزيارات وحالة المبيعات الخاصة بك' : 'Track your property listings, views, and sales performance'); ?>

                    </p>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <?php if($isPending): ?>
                    <button class="btn btn-secondary disabled" style="opacity: 0.65; border-radius: 8px; font-weight: 600;" title="<?php echo e($isAr ? 'لا يمكن الإضافة حتى يتم تفعيل حسابك' : 'Pending activation'); ?>">
                        + <?php echo e($isAr ? 'إضافة عقار (بانتظار التفعيل)' : 'Add Property (Pending)'); ?>

                    </button>
                <?php else: ?>
                    <a href="<?php echo e(route('dashboard.properties.create', ['locale' => $locale])); ?>" class="btn btn-primary" style="background: #db1d23; border-color: #db1d23; border-radius: 8px; font-weight: 700; padding: 10px 20px;">
                        + <?php echo e($isAr ? 'إضافة عقار جديد' : 'Add Property'); ?>

                    </a>
                <?php endif; ?>

                <?php if($isCompany): ?>
                    <?php if($isPending): ?>
                        <button class="btn btn-outline-light disabled" style="opacity: 0.65; border-radius: 8px; font-weight: 600;">
                            + <?php echo e($isAr ? 'إضافة مشروع (معلق)' : 'Add Project (Pending)'); ?>

                        </button>
                    <?php else: ?>
                        <a href="<?php echo e(route('dashboard.projects.create', ['locale' => $locale])); ?>" class="btn btn-outline-light" style="border-radius: 8px; font-weight: 600; padding: 10px 18px;">
                            + <?php echo e($isAr ? 'إضافة مشروع جديد' : 'Add Project'); ?>

                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <!-- Total Properties -->
        <div class="col-6 col-md-4 <?php if($isCompany): ?> col-lg <?php else: ?> col-lg-3 <?php endif; ?>">
            <div class="card shadow-sm border-0 p-3 h-100" style="border-radius: 14px; background: #ffffff;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13.5px;"><?php echo e($isAr ? 'إجمالي العقارات' : 'Total Properties'); ?></span>
                    <span style="font-size: 20px;">🏠</span>
                </div>
                <div class="h3 fw-bold mb-0 text-dark"><?php echo e(number_format($totalProperties)); ?></div>
                <a href="<?php echo e(route('dashboard.properties.index', ['locale' => $locale])); ?>" class="text-decoration-none mt-2 d-inline-block text-muted" style="font-size: 12px;">
                    <?php echo e($isAr ? 'عرض الكل' : 'View all'); ?> ←
                </a>
            </div>
        </div>

        <!-- Active / Published Listings -->
        <div class="col-6 col-md-4 <?php if($isCompany): ?> col-lg <?php else: ?> col-lg-3 <?php endif; ?>">
            <div class="card shadow-sm border-0 p-3 h-100" style="border-radius: 14px; background: #ffffff;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13.5px;"><?php echo e($isAr ? 'معروضة بالموقع' : 'Published'); ?></span>
                    <span style="font-size: 20px;">🟢</span>
                </div>
                <div class="h3 fw-bold mb-0 text-success"><?php echo e(number_format($activeProperties)); ?></div>
                <a href="<?php echo e(route('dashboard.properties.index', ['locale' => $locale, 'status' => 'published'])); ?>" class="text-decoration-none mt-2 d-inline-block text-muted" style="font-size: 12px;">
                    <?php echo e($isAr ? 'العروض النشطة' : 'Active listings'); ?> ←
                </a>
            </div>
        </div>

        <!-- Sold Properties -->
        <div class="col-6 col-md-4 <?php if($isCompany): ?> col-lg <?php else: ?> col-lg-3 <?php endif; ?>">
            <div class="card shadow-sm border-0 p-3 h-100" style="border-radius: 14px; background: #ffffff;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13.5px;"><?php echo e($isAr ? 'عقارات تم بيعها' : 'Sold Properties'); ?></span>
                    <span style="font-size: 20px;">🤝</span>
                </div>
                <div class="h3 fw-bold mb-0" style="color: #0d9488;"><?php echo e(number_format($soldProperties)); ?></div>
                <a href="<?php echo e(route('dashboard.properties.index', ['locale' => $locale, 'status' => 'sold'])); ?>" class="text-decoration-none mt-2 d-inline-block text-muted" style="font-size: 12px;">
                    <?php echo e($isAr ? 'المبيعات المنفذة' : 'Completed sales'); ?> ←
                </a>
            </div>
        </div>

        <!-- Total Views / Visits (الزيارات والمشاهدات) -->
        <div class="col-6 col-md-4 <?php if($isCompany): ?> col-lg <?php else: ?> col-lg-3 <?php endif; ?>">
            <div class="card shadow-sm border-0 p-3 h-100" style="border-radius: 14px; background: #ffffff;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13.5px;"><?php echo e($isAr ? 'إجمالي المشاهدات' : 'Total Views'); ?></span>
                    <span style="font-size: 20px;">👁️</span>
                </div>
                <div class="h3 fw-bold mb-0" style="color: #2563eb;"><?php echo e(number_format($totalViews)); ?></div>
                <span class="text-muted mt-2 d-inline-block" style="font-size: 12px;">
                    <?php echo e($isAr ? 'زيارات على عقاراتك' : 'Visits on listings'); ?>

                </span>
            </div>
        </div>

        <!-- Company Projects (If Company) -->
        <?php if($isCompany): ?>
            <div class="col-12 col-md-4 col-lg">
                <div class="card shadow-sm border-0 p-3 h-100" style="border-radius: 14px; background: #ffffff;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-semibold" style="font-size: 13.5px;"><?php echo e($isAr ? 'مشاريع الشركة' : 'Company Projects'); ?></span>
                        <span style="font-size: 20px;">🏗️</span>
                    </div>
                    <div class="h3 fw-bold mb-0" style="color: #C8A45C;"><?php echo e(number_format($totalProjects)); ?></div>
                    <a href="<?php echo e(route('dashboard.projects.index', ['locale' => $locale])); ?>" class="text-decoration-none mt-2 d-inline-block text-muted" style="font-size: 12px;">
                        <?php echo e($isAr ? 'إدارة المشاريع' : 'Manage projects'); ?> ←
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" style="font-size: 14px;">
        <li class="nav-item">
            <a class="nav-link active fw-bold" href="<?php echo e(route('dashboard.index', ['locale' => $locale])); ?>" style="background: #111e33; border-radius: 8px;">
                <?php echo e($isAr ? 'نظرة عامة' : 'Overview'); ?>

            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark fw-semibold" href="<?php echo e(route('dashboard.properties.index', ['locale' => $locale])); ?>">
                <?php echo e($isAr ? 'عقاراتي' : 'My Properties'); ?> (<?php echo e($totalProperties); ?>)
            </a>
        </li>
        <?php if($isCompany): ?>
            <li class="nav-item">
                <a class="nav-link text-dark fw-semibold" href="<?php echo e(route('dashboard.projects.index', ['locale' => $locale])); ?>">
                    <?php echo e($isAr ? 'مشاريع الشركة' : 'Projects'); ?> (<?php echo e($totalProjects); ?>)
                </a>
            </li>
        <?php endif; ?>
    </ul>

    
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 14px; overflow: hidden;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="font-size: 1.05rem;">
                <?php echo e($isAr ? 'آخر العقارات المضافة وتفاصيل الزيارات' : 'Recent Listings & Views Performance'); ?>

            </h5>
            <a href="<?php echo e(route('dashboard.properties.index', ['locale' => $locale])); ?>" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; font-size: 12.5px;">
                <?php echo e($isAr ? 'عرض الكل' : 'View All'); ?>

            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                <thead style="background: #F8FAFC;">
                    <tr>
                        <th style="padding: 14px 18px;">كود العقار</th>
                        <th>اسم العقار والموقع</th>
                        <th>السعر</th>
                        <th>الزيارات (المشاهدات)</th>
                        <th>الحالة</th>
                        <th class="text-end" style="padding-left: 18px;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentProperties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $st = $p->status?->value ?? (string) $p->status;
                        ?>
                        <tr>
                            <td style="padding: 14px 18px;">
                                <code><?php echo e($p->reference_code); ?></code>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo e($p->translation()?->name ?: 'عقار بدون اسم'); ?></div>
                                <div class="text-muted" style="font-size: 12px;"><?php echo e($p->location?->translation()?->name ?? '—'); ?></div>
                            </td>
                            <td>
                                <?php if($p->isPriceOnContact()): ?>
                                    <span class="badge text-bg-secondary">عند التواصل</span>
                                <?php else: ?>
                                    <span class="fw-bold"><?php echo e(money_eg($p->price)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1 fw-bold text-primary" style="font-size: 13.5px;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <?php echo e(number_format($p->views_count)); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($st === 'published'): ?>
                                    <span class="badge" style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; font-size: 12px; padding: 5px 10px; border-radius: 20px;">
                                        ✓ معتمد ومعروض
                                    </span>
                                <?php elseif($st === 'sold'): ?>
                                    <span class="badge" style="background: #CCFBF1; color: #0F766E; border: 1px solid #99F6E4; font-size: 12px; padding: 5px 10px; border-radius: 20px;">
                                        🤝 تم البيع
                                    </span>
                                <?php elseif($st === 'pending_review'): ?>
                                    <span class="badge" style="background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; font-size: 12px; padding: 5px 10px; border-radius: 20px;">
                                        ⏳ قيد المراجعة
                                    </span>
                                <?php elseif($st === 'rejected'): ?>
                                    <span class="badge" style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; font-size: 12px; padding: 5px 10px; border-radius: 20px;">
                                        ✕ مرفوض
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: #F1F5F9; color: #475569; font-size: 12px; padding: 5px 10px; border-radius: 20px;">
                                        <?php echo e($st); ?>

                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end" style="padding-left: 18px;">
                                <div class="d-inline-flex gap-1">
                                    <?php if($st === 'published'): ?>
                                        <a href="<?php echo e(route('properties.show', ['locale' => $locale, 'slug' => $p->translation()?->slug ?? $p->id])); ?>"
                                            class="btn btn-sm btn-outline-success" target="_blank"
                                            style="border-radius: 6px; font-size: 11.5px;">
                                            عرض بالموقع ↗
                                        </a>

                                        
                                        <form method="POST" action="<?php echo e(route('dashboard.properties.sold', ['locale' => $locale, 'property' => $p])); ?>" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من تحديد هذا العقار كـ «تم البيع»؟');">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-teal" style="border: 1px solid #0d9488; color: #0d9488; border-radius: 6px; font-size: 11.5px;">
                                                تم البيع ✓
                                            </button>
                                        </form>
                                    <?php elseif($st === 'sold'): ?>
                                        <form method="POST" action="<?php echo e(route('dashboard.properties.available', ['locale' => $locale, 'property' => $p])); ?>" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; font-size: 11.5px;">
                                                إعادة العرض
                                            </button>
                                        </form>
                                    <?php elseif($st === 'draft' || $st === 'rejected'): ?>
                                        <form method="POST" action="<?php echo e(route('dashboard.properties.submit', ['locale' => $locale, 'property' => $p])); ?>" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-sm btn-outline-primary" style="border-radius: 6px; font-size: 11.5px;">إرسال للمراجعة</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                لا توجد عقارات مضافة لديك بعد. اضغط على «+ إضافة عقار جديد» للبدء.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard/index.blade.php ENDPATH**/ ?>