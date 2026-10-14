<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('page_title', $title); ?>
<?php $__env->startSection('page_subtitle', $subtitle); ?>

<?php
    $activeTab = request()->query('tab', 'items');
?>

<?php $__env->startSection('page_actions'); ?>
    <div class="d-flex gap-2">
        <a href="/ar/faqs" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'external', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            عرض الصفحة بالموقع
        </a>
        <a href="<?php echo e(admin_route('admin.faqs.create')); ?>" class="ap-btn ap-btn--primary ap-btn--sm">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'plus', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            إضافة سؤال جديد
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="ap-tabs mb-4">
        <a href="<?php echo e(admin_route('admin.faqs.index', ['tab' => 'items'])); ?>"
            class="ap-tab <?php echo e($activeTab === 'items' ? 'is-active' : ''); ?>">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'help-circle', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            قائمة الأسئلة (<?php echo e(count($items)); ?>)
        </a>
        <a href="<?php echo e(admin_route('admin.faqs.index', ['tab' => 'settings'])); ?>"
            class="ap-tab <?php echo e($activeTab === 'settings' ? 'is-active' : ''); ?>">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'settings', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            إعدادات نصوص الصفحة والترويسة
        </a>
    </div>

    <?php if($activeTab === 'items'): ?>
        
        <div class="ap-card mb-4">
            <div class="ap-card__body py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted fw-bold" style="font-size: 13px;">تصفية حسب القسم:</span>
                    <a href="<?php echo e(admin_route('admin.faqs.index', ['tab' => 'items', 'category' => 'all'])); ?>"
                        class="ap-btn <?php echo e($selectedCategory === 'all' ? 'ap-btn--primary' : 'ap-btn--ghost'); ?> ap-btn--sm"
                        style="border-radius: 9999px;">
                        الكل (<?php echo e(count($items)); ?>)
                    </a>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(admin_route('admin.faqs.index', ['tab' => 'items', 'category' => $cat])); ?>"
                            class="ap-btn <?php echo e($selectedCategory === $cat ? 'ap-btn--primary' : 'ap-btn--ghost'); ?> ap-btn--sm"
                            style="border-radius: 9999px;">
                            <?php echo e($cat); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'help-circle', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    الأسئلة المسجلة
                </h2>
                <span class="ap-badge"><?php echo e(count($items)); ?> سؤال</span>
            </div>

            <div class="ap-card__body p-0">
                <?php if(empty($items)): ?>
                    <div class="ap-empty py-5 text-center">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'help-circle', 'size' => 48], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <p class="mt-2 text-muted">لا توجد أسئلة مسجلة في هذا القسم.</p>
                        <a href="<?php echo e(admin_route('admin.faqs.create')); ?>" class="ap-btn ap-btn--primary ap-btn--sm mt-3">
                            إضافة سؤال جديد
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="ap-table w-100">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="width: 180px;">القسم</th>
                                    <th>السؤال</th>
                                    <th>مقتطف الإجابة</th>
                                    <th style="width: 130px; text-align: left;">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <span class="text-muted fw-bold"><?php echo e($idx + 1); ?></span>
                                        </td>
                                        <td>
                                            <span class="ap-badge"
                                                style="background: rgba(245, 158, 11, 0.15); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.3);">
                                                <?php echo e($item['category'] ?? 'عام'); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold"
                                                style="color: var(--ap-text);"><?php echo e($item['question'] ?? ''); ?></span>
                                        </td>
                                        <td>
                                            <span class="text-muted"
                                                style="font-size: 13px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                <?php echo e($item['answer'] ?? ''); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <?php $itemId = $item['id'] ?? ($idx + 1); ?>
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="<?php echo e(admin_route('admin.faqs.edit', $itemId)); ?>"
                                                    class="ap-btn ap-btn--ghost ap-btn--xs" title="تعديل">
                                                    <?php echo $__env->make('admin.partials.icon', [
                                                        'iconName' => 'edit',
                                                        'size' => 14,
                                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </a>
                                                <form method="post"
                                                    action="<?php echo e(admin_route('admin.faqs.destroy', $itemId)); ?>"
                                                    onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟');"
                                                    style="display: inline-block;">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('delete'); ?>
                                                    <button type="submit"
                                                        class="ap-btn ap-btn--ghost ap-btn--xs text-danger" title="حذف">
                                                        <?php echo $__env->make('admin.partials.icon', [
                                                            'iconName' => 'trash',
                                                            'size' => 14,
                                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        
        <form method="post" action="<?php echo e(admin_route('admin.faqs.settings.update')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('put'); ?>

            <div class="ap-grid ap-grid--2" style="align-items: start;">
                
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'layout', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            إعدادات ترويسة الصفحة (Hero Section)
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">شارة الترويسة العليا (Badge)</label>
                            <input type="text" name="faq_hero_badge" class="ap-input"
                                value="<?php echo e(old('faq_hero_badge', setting('faq.hero_badge', 'مركز المساعدة ودليل المستخدمين'))); ?>">
                            <p class="ap-help">النص الصغير الذي يظهر أعلى العنوان مع أيقونة المساعدة.</p>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">العنوان الرئيسي <span>*</span></label>
                            <input type="text" name="faq_hero_title" class="ap-input" required
                                value="<?php echo e(old('faq_hero_title', setting('faq.hero_title', 'الأسئلة الشائعة والأجوبة الوافية'))); ?>">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">الوصف التوضيحي</label>
                            <textarea name="faq_hero_subtitle" class="ap-textarea" rows="3"><?php echo e(old('faq_hero_subtitle', setting('faq.hero_subtitle', 'إجابات شاملة على أهم الأسئلة حول شراء وبيع وتأجير العقارات، الأمور القانونية، والتعامل مع الوكلاء المعتمدين في بني سويف ومصر.'))); ?></textarea>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">نص حقل البحث التوضيحي (Placeholder)</label>
                            <input type="text" name="faq_search_placeholder" class="ap-input"
                                value="<?php echo e(old('faq_search_placeholder', setting('faq.search_placeholder', 'ابحث في الأسئلة الشائعة.. مثل: سند الملكية، الاستثمار في بني سويف، الإيجار ...'))); ?>">
                        </div>
                    </div>
                </div>

                
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'phone', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            إعدادات صندوق الدعم والتواصل السفلي
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">شارة الصندوق (Badge)</label>
                            <input type="text" name="faq_cta_badge" class="ap-input"
                                value="<?php echo e(old('faq_cta_badge', setting('faq.cta_badge', 'ما زلت تبحث عن إجابة؟'))); ?>">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">عنوان صندوق الدعم <span>*</span></label>
                            <input type="text" name="faq_cta_title" class="ap-input" required
                                value="<?php echo e(old('faq_cta_title', setting('faq.cta_title', 'فريقنا مستعد لمساعدتك دائماً'))); ?>">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">وصف صندوق الدعم</label>
                            <textarea name="faq_cta_desc" class="ap-textarea" rows="3"><?php echo e(old('faq_cta_desc', setting('faq.cta_desc', 'تواصل مع فريق الدعم لدينا للحصول على استشارة مجانية وإجابات دقيقة لجميع استفساراتك العقارية في بني سويف ومصر.'))); ?></textarea>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">نص زر التواصل <span>*</span></label>
                            <input type="text" name="faq_cta_btn_text" class="ap-input" required
                                value="<?php echo e(old('faq_cta_btn_text', setting('faq.cta_btn_text', 'تواصل معنا الآن'))); ?>">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">رابط زر التواصل <span>*</span></label>
                            <input type="text" name="faq_cta_btn_url" class="ap-input" required
                                value="<?php echo e(old('faq_cta_btn_url', setting('faq.cta_btn_url', '/ar/contact-us'))); ?>">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">نص زر الواتساب <span>*</span></label>
                            <input type="text" name="faq_cta_whatsapp_text" class="ap-input" required
                                value="<?php echo e(old('faq_cta_whatsapp_text', setting('faq.cta_whatsapp_text', 'محادثة واتساب سريعة'))); ?>">
                            <p class="ap-help">رقم الواتساب يُجلب تلقائياً من إعدادات التواصل العامة.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="ap-btn ap-btn--primary">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'check', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    حفظ إعدادات الصفحة
                </button>
            </div>
        </form>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/faqs/index.blade.php ENDPATH**/ ?>