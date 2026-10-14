<?php
    $isEdit = $property->exists;
    $ar = $property->translations->firstWhere('locale', 'ar') ?? new \App\Models\PropertyTranslation(['locale' => 'ar']);
    $en = $property->translations->firstWhere('locale', 'en') ?? new \App\Models\PropertyTranslation(['locale' => 'en']);
    $oldValue = fn (string $key, $fallback = null) => old($key, $fallback);
    $selectedAmenities = old('amenities', $property->exists ? $property->amenities->pluck('id')->all() : []);
?>

<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('page_title', $title); ?>
<?php $__env->startSection('page_subtitle', $subtitle ?? 'كل بيانات العقار: الموقع، السعر، التفاصيل، الصور، والإسناد'); ?>

<?php $__env->startSection('page_actions'); ?>
    <a href="<?php echo e(admin_route('admin.properties.index')); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع للقائمة</a>
    <?php if($isEdit): ?>
        <a href="<?php echo e(admin_route('admin.properties.stats', $property)); ?>" class="ap-btn ap-btn--ghost ap-btn--sm">الإحصائيات</a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <form method="post" enctype="multipart/form-data"
        action="<?php echo e($isEdit ? admin_route('admin.properties.update', $property) : admin_route('admin.properties.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?>
            <?php echo method_field('put'); ?>
        <?php endif; ?>

        <div class="ap-grid ap-grid--2" style="align-items: start;">
            
            <div>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'building', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> البيانات الأساسية</h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">كود العقار</label>
                                <input type="text" name="reference_code" class="ap-input"
                                    value="<?php echo e($oldValue('reference_code', $property->reference_code)); ?>"
                                    placeholder="يُولَّد تلقائياً إذا تُرك فارغاً">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الغرض <span>*</span></label>
                                <select name="purpose" class="ap-select" required>
                                    <option value="sale" <?php if($oldValue('purpose', $property->purpose) === 'sale'): echo 'selected'; endif; ?>>بيع</option>
                                    <option value="rent" <?php if($oldValue('purpose', $property->purpose) === 'rent'): echo 'selected'; endif; ?>>إيجار</option>
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الحالة <span>*</span></label>
                                <select name="status" class="ap-select" required>
                                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($status->value); ?>" <?php if($oldValue('status', $property->status?->value) === $status->value): echo 'selected'; endif; ?>>
                                            <?php echo e(status_label($status)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">نوع السعر / سياسة التسعير <span>*</span></label>
                                <select name="price_type" id="propertyPriceTypeSelect" class="ap-select" required>
                                    <option value="fixed" <?php if((string) $oldValue('price_type', $property->price_type) === 'fixed'): echo 'selected'; endif; ?>>
                                        محدد (سعر نهائي وثابت)
                                    </option>
                                    <option value="negotiable" <?php if((string) $oldValue('price_type', $property->price_type) === 'negotiable'): echo 'selected'; endif; ?>>
                                        قابل للتفاوض (يوجد مجال للتفاوض)
                                    </option>
                                    <option value="contact" <?php if((string) $oldValue('price_type', $property->price_type) === 'contact'): echo 'selected'; endif; ?>>
                                        يتم تحديده من خلال التواصل (السعر عند الطلب)
                                    </option>
                                </select>
                                <small class="text-muted" style="font-size: 0.8rem; display: block; margin-top: 4px;">
                                    حدد ما إذا كان السعر معلناً وثابتاً، أو قابلاً للتفاوض، أو مخفياً يظهر "السعر عند التواصل".
                                </small>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">العملة <span>*</span></label>
                                <select name="currency_id" class="ap-select" required>
                                    <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($currency->id); ?>" <?php if((string) $oldValue('currency_id', $property->currency_id) === (string) $currency->id): echo 'selected'; endif; ?>>
                                            <?php echo e($currency->code); ?> — <?php echo e($currency->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="ap-field" id="propertyPriceField">
                                <label class="ap-label">السعر <span id="propertyPriceRequiredAsterisk">*</span></label>
                                <input type="number" step="0.01" min="0" name="price" id="propertyPriceInput" class="ap-input" required
                                    value="<?php echo e($oldValue('price', $property->price)); ?>">
                                <small id="propertyPriceHint" class="text-muted" style="font-size: 0.8rem; display: block; margin-top: 4px;"></small>
                            </div>
                            <div class="ap-field ap-col-span-full" style="grid-column: 1 / -1; background: rgba(245, 158, 11, 0.08); border: 1px dashed rgba(217, 119, 6, 0.45); padding: 14px; border-radius: 8px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; flex-wrap: wrap; gap: 8px;">
                                    <label class="ap-label" style="margin: 0; color: #b45309; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        ملاحظات سرية خاصة بالسعر والتفاوض (للإدارة والوسطاء فقط)
                                    </label>
                                    <span class="ap-badge ap-badge--warning" style="font-size: 11px;">
                                        🔒 سرية تماماً - لا تظهر للعميل المشتري أو الزوار نهائياً
                                    </span>
                                </div>
                                <textarea name="price_notes" class="ap-textarea" rows="2" placeholder="اكتب ما تم الاتفاق عليه بينك وبين العميل بخصوص السعر والتفاوض (مثال: العميل متفق ينزل لـ 1,800,000 كاش عند الجدية، أو السعر يشمل الفرش، أو عمولة الوسيط 2.5%...)"><?php echo e($oldValue('price_notes', $property->price_notes)); ?></textarea>
                                <div style="font-size: 0.78rem; color: #92400e; margin-top: 4px;">
                                    تساعد مسؤولي المبيعات والوسطاء على معرفة حدود التفاوض المتفق عليها مع المالك دون الحاجة للرجوع إليه في كل مكالمة.
                                </div>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">تاريخ الانتهاء</label>
                                <input type="date" name="expires_at" class="ap-input"
                                    value="<?php echo e($oldValue('expires_at', $property->expires_at?->format('Y-m-d'))); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">التصنيف</label>
                                <select name="category_id" class="ap-select">
                                    <option value="">— بدون —</option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($category->id); ?>" <?php if((string) $oldValue('category_id', $property->category_id) === (string) $category->id): echo 'selected'; endif; ?>>
                                            <?php echo e($category->translation('ar')?->name ?? $category->key); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">نوع العقار</label>
                                <select name="property_type_id" class="ap-select">
                                    <option value="">— بدون —</option>
                                    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type->id); ?>" <?php if((string) $oldValue('property_type_id', $property->property_type_id) === (string) $type->id): echo 'selected'; endif; ?>>
                                            <?php echo e($type->translation('ar')?->name ?? $type->key); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الموقع / المنطقة</label>
                                <select name="location_id" class="ap-select">
                                    <option value="">— بدون —</option>
                                    <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($location->id); ?>" <?php if((string) $oldValue('location_id', $property->location_id) === (string) $location->id): echo 'selected'; endif; ?>>
                                            <?php echo e($location->translation('ar')?->name ?? ('#' . $location->id)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">المشروع</label>
                                <select name="project_id" class="ap-select">
                                    <option value="">— بدون —</option>
                                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($project->id); ?>" <?php if((string) $oldValue('project_id', $property->project_id) === (string) $project->id): echo 'selected'; endif; ?>>
                                            <?php echo e($project->translation('ar')?->name ?? $project->reference_code); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <label class="ap-check" style="margin-top: 6px;">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" <?php if($oldValue('is_featured', $property->is_featured)): echo 'checked'; endif; ?>>
                            عقار مميز (يظهر في الصفحة الرئيسية)
                        </label>
                    </div>
                </div>

                
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('property.assign')): ?>
                    <div class="ap-card">
                        <div class="ap-card__head">
                            <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'users', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> الإسناد والملكية</h2>
                        </div>
                        <div class="ap-card__body">
                            <p class="text-muted" style="font-size: 12.5px;">
                                العقار يجب أن يكون مُسنداً لمسؤول أو سمسار. الأدمن يستطيع الإسناد لأي مستخدم، والمسؤول يُسند فقط لسماسرته.
                            </p>
                            <div class="ap-grid ap-grid--2">
                                <div class="ap-field">
                                    <label class="ap-label">المالك / السمسار</label>
                                    <select name="owner_id" class="ap-select">
                                        <option value="">— بدون —</option>
                                        <?php $__currentLoopData = $assignableUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($assignee->id); ?>" <?php if((string) $oldValue('owner_id', $property->owner_id) === (string) $assignee->id): echo 'selected'; endif; ?>>
                                                <?php echo e($assignee->name); ?> — <?php echo e(user_role_label($assignee->primary_role_slug)); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="ap-field">
                                    <label class="ap-label">المسؤول المتابع</label>
                                    <select name="managed_by" class="ap-select">
                                        <option value="">— بدون —</option>
                                        <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($manager->id); ?>" <?php if((string) $oldValue('managed_by', $property->managed_by) === (string) $manager->id): echo 'selected'; endif; ?>>
                                                <?php echo e($manager->name); ?> — <?php echo e(user_role_label($manager->primary_role_slug)); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <?php if($isEdit): ?>
                                <div class="ap-alert ap-alert--info" style="margin-bottom: 0;">
                                    <div>
                                        <strong>حالة الإسناد:</strong> <?php echo e($property->assignmentLabel()); ?>

                                        <?php if($property->last_assigned_at): ?>
                                            — آخر إسناد <?php echo e($property->last_assigned_at->diffForHumans()); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'photo', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> صور العقار</h2>
                        <span class="ap-badge"><?php echo e($property->exists ? $property->media->count() : 0); ?> صورة</span>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">إضافة صور</label>
                            <input type="file" name="images[]" class="ap-input" accept="image/*" multiple>
                            <div class="ap-help">الصيغ: jpg, png, webp — أقصى حجم <?php echo e(setting('storage.max_upload_mb', 8)); ?> ميجابايت للصورة.</div>
                        </div>

                        <?php if($isEdit && $property->media->isNotEmpty()): ?>
                            <div class="ap-thumb-grid">
                                <?php $__currentLoopData = $property->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="ap-thumb">
                                        <img src="<?php echo e(str_starts_with((string) $media->path, 'http') ? $media->path : asset('storage/' . ltrim($media->path, '/'))); ?>"
                                            alt="" loading="lazy"
                                            onerror="this.src='<?php echo e($property->placeholderUrl('ar')); ?>'">
                                        <?php if($media->is_primary): ?>
                                            <span class="ap-badge ap-badge--gold ap-thumb__badge">رئيسية</span>
                                        <?php endif; ?>
                                        <div class="ap-thumb__actions">
                                            <?php if(!$media->is_primary): ?>
                                                <button type="submit" form="mediaPrimary<?php echo e($media->id); ?>"
                                                    class="ap-btn ap-btn--ghost ap-btn--sm">تعيين رئيسية</button>
                                            <?php endif; ?>
                                            <button type="submit" form="mediaDelete<?php echo e($media->id); ?>"
                                                class="ap-btn ap-btn--danger ap-btn--sm">حذف</button>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php elseif(!$isEdit): ?>
                            <div class="ap-alert ap-alert--info" style="margin: 0;">سيتم رفع الصور بعد حفظ العقار أول مرة.</div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="ap-card" id="floorPlansCard" style="margin-top: 18px;">
                    <div class="ap-card__head" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <h2 class="ap-card__title" style="margin: 0; display: flex; align-items: center; gap: 6px;">
                            <svg style="width: 18px; height: 18px; color: #b45309;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                <line x1="9" y1="21" x2="9" y2="9"></line>
                            </svg>
                            التخطيط الهندسي للشقة (Floor Plans)
                        </h2>
                        <button type="button" class="ap-btn ap-btn--ghost ap-btn--sm" id="addFloorPlanBtn" style="font-size: 12px; padding: 4px 10px;">
                            + إضافة مخطط هندسي
                        </button>
                    </div>
                    <div class="ap-card__body">
                        <input type="hidden" name="floor_plans_submitted" value="1">
                        <p class="text-muted" style="font-size: 13px; margin-bottom: 14px;">
                            أضف التخطيطات الهندسية والمعمارية للشقة (مخطط الشقة، توزيع الغرف، الأبعاد) مع صورة المخطط.
                        </p>

                        <div id="floorPlansContainer" style="display: flex; flex-direction: column; gap: 14px;">
                            <?php
                                $existingPlans = old('floor_plans', $property->exists ? $property->getFloorPlans() : []);
                            ?>

                            <?php $__empty_1 = true; $__currentLoopData = $existingPlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $fp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="floor-plan-row" data-plan-index="<?php echo e($idx); ?>" style="background: rgba(248, 250, 252, 0.8); border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <strong style="font-size: 13px; color: #334155;" class="plan-number-label">📐 مخطط هندسي #<?php echo e($loop->iteration); ?></strong>
                                        <button type="button" class="ap-btn ap-btn--danger ap-btn--sm remove-floor-plan-btn" style="padding: 2px 8px; font-size: 11px;">
                                            حذف
                                        </button>
                                    </div>
                                    <div class="ap-grid ap-grid--2" style="margin-bottom: 10px;">
                                        <div class="ap-field">
                                            <label class="ap-label">اسم / عنوان المخطط</label>
                                            <input type="text" name="floor_plans[<?php echo e($idx); ?>][name]" class="ap-input"
                                                value="<?php echo e($fp['name'] ?? ''); ?>" placeholder="مثال: المخطط المعماري للشقة">
                                        </div>
                                        <div class="ap-field">
                                            <label class="ap-label">المساحة (م²)</label>
                                            <input type="number" step="0.1" min="0" name="floor_plans[<?php echo e($idx); ?>][area]" class="ap-input"
                                                value="<?php echo e($fp['area'] ?? ''); ?>" placeholder="140">
                                        </div>
                                        <div class="ap-field">
                                            <label class="ap-label">غرف النوم</label>
                                            <input type="number" min="0" name="floor_plans[<?php echo e($idx); ?>][bedrooms]" class="ap-input"
                                                value="<?php echo e($fp['bedrooms'] ?? ''); ?>" placeholder="3">
                                        </div>
                                        <div class="ap-field">
                                            <label class="ap-label">الحمامات</label>
                                            <input type="number" min="0" name="floor_plans[<?php echo e($idx); ?>][bathrooms]" class="ap-input"
                                                value="<?php echo e($fp['bathrooms'] ?? ''); ?>" placeholder="2">
                                        </div>
                                    </div>

                                    <div class="ap-field" style="margin-bottom: 10px;">
                                        <label class="ap-label">صورة التخطيط الهندسي</label>
                                        <?php if(!empty($fp['image'])): ?>
                                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                                <img src="<?php echo e(!empty($fp['image_url']) ? $fp['image_url'] : asset('storage/' . ltrim($fp['image'], '/'))); ?>"
                                                     alt="Floor Plan" style="height: 60px; width: 90px; object-fit: contain; background: #fff; border-radius: 6px; border: 1px solid #cbd5e1; padding: 2px;">
                                                <input type="hidden" name="floor_plans[<?php echo e($idx); ?>][existing_image]" value="<?php echo e($fp['image']); ?>">
                                                <span style="font-size: 12px; color: #64748b;">توجد صورة محفوظة. لرفع صورة بديلة:</span>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" name="floor_plans[<?php echo e($idx); ?>][image]" class="ap-input" accept="image/*">
                                    </div>

                                    <div class="ap-field" style="margin-bottom: 0;">
                                        <label class="ap-label">ملاحظات أو مواصفات المخطط</label>
                                        <textarea name="floor_plans[<?php echo e($idx); ?>][description]" class="ap-textarea" rows="2"
                                            placeholder="تفاصيل إضافية عن التقسيم الداخلي والأبعاد..."><?php echo e($fp['description'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="floor-plan-row" data-plan-index="0" style="background: rgba(248, 250, 252, 0.8); border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <strong style="font-size: 13px; color: #334155;" class="plan-number-label">📐 مخطط هندسي #1</strong>
                                        <button type="button" class="ap-btn ap-btn--danger ap-btn--sm remove-floor-plan-btn" style="padding: 2px 8px; font-size: 11px;">
                                            حذف
                                        </button>
                                    </div>
                                    <div class="ap-grid ap-grid--2" style="margin-bottom: 10px;">
                                        <div class="ap-field">
                                            <label class="ap-label">اسم / عنوان المخطط</label>
                                            <input type="text" name="floor_plans[0][name]" class="ap-input" placeholder="مثال: المخطط المعماري للشقة">
                                        </div>
                                        <div class="ap-field">
                                            <label class="ap-label">المساحة (م²)</label>
                                            <input type="number" step="0.1" min="0" name="floor_plans[0][area]" class="ap-input" placeholder="140">
                                        </div>
                                        <div class="ap-field">
                                            <label class="ap-label">غرف النوم</label>
                                            <input type="number" min="0" name="floor_plans[0][bedrooms]" class="ap-input" placeholder="3">
                                        </div>
                                        <div class="ap-field">
                                            <label class="ap-label">الحمامات</label>
                                            <input type="number" min="0" name="floor_plans[0][bathrooms]" class="ap-input" placeholder="2">
                                        </div>
                                    </div>

                                    <div class="ap-field" style="margin-bottom: 10px;">
                                        <label class="ap-label">صورة التخطيط الهندسي</label>
                                        <input type="file" name="floor_plans[0][image]" class="ap-input" accept="image/*">
                                    </div>

                                    <div class="ap-field" style="margin-bottom: 0;">
                                        <label class="ap-label">ملاحظات أو مواصفات المخطط</label>
                                        <textarea name="floor_plans[0][description]" class="ap-textarea" rows="2"
                                            placeholder="تفاصيل إضافية عن التقسيم الداخلي والأبعاد..."></textarea>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'tag', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> التفاصيل والمساحات</h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-grid ap-grid--3">
                            <div class="ap-field">
                                <label class="ap-label">غرف النوم</label>
                                <input type="number" min="0" name="bedrooms" class="ap-input" value="<?php echo e($oldValue('bedrooms', $property->bedrooms)); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الحمامات</label>
                                <input type="number" min="0" name="bathrooms" class="ap-input" value="<?php echo e($oldValue('bathrooms', $property->bathrooms)); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">أماكن الانتظار</label>
                                <input type="number" min="0" name="parking_spaces" class="ap-input" value="<?php echo e($oldValue('parking_spaces', $property->parking_spaces)); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">المساحة</label>
                                <input type="number" step="0.01" min="0" name="area" class="ap-input" value="<?php echo e($oldValue('area', $property->area)); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">وحدة المساحة</label>
                                <select name="area_unit" class="ap-select">
                                    <?php $__currentLoopData = ['m2' => 'متر مربع', 'ft2' => 'قدم مربع', 'acre' => 'فدان', 'hectare' => 'هكتار']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($unit); ?>" <?php if($oldValue('area_unit', $property->area_unit ?? 'm2') === $unit): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">مساحة الأرض</label>
                                <input type="number" step="0.01" min="0" name="land_area" class="ap-input" value="<?php echo e($oldValue('land_area', $property->land_area)); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">عدد الأدوار</label>
                                <input type="number" min="0" name="floors_count" class="ap-input" value="<?php echo e($oldValue('floors_count', $property->floors_count)); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الدور</label>
                                <input type="number" name="floor_number" class="ap-input" value="<?php echo e($oldValue('floor_number', $property->floor_number)); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">سنة البناء</label>
                                <input type="number" min="1800" max="2100" name="year_built" class="ap-input" value="<?php echo e($oldValue('year_built', $property->year_built)); ?>">
                            </div>
                        </div>

                        
                        <?php echo $__env->make('admin.partials.map-picker', [
                            'latId' => 'prop-latitude',
                            'lngId' => 'prop-longitude',
                            'latName' => 'latitude',
                            'lngName' => 'longitude',
                            'latValue' => $oldValue('latitude', $property->latitude),
                            'lngValue' => $oldValue('longitude', $property->longitude),
                            'containerId' => 'prop-map-picker-box',
                            'mapId' => 'prop-leaflet-picker-map',
                            'defaultLat' => 29.0724,
                            'defaultLng' => 31.0962,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="ap-field">
                            <label class="ap-label">المرافق والخدمات</label>
                            <div class="ap-grid ap-grid--3">
                                <?php $__currentLoopData = $amenities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $amenity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="ap-check">
                                        <input type="checkbox" name="amenities[]" value="<?php echo e($amenity->id); ?>"
                                            <?php if(in_array($amenity->id, $selectedAmenities)): echo 'checked'; endif; ?>>
                                        <?php echo e($amenity->translation('ar')?->name ?? $amenity->key); ?>

                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">ملاحظات الإدارة (لا تظهر للزوار)</label>
                            <textarea name="admin_note" class="ap-textarea" rows="3"><?php echo e($oldValue('admin_note', $property->admin_note)); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title"><?php echo $__env->make('admin.partials.icon', ['iconName' => 'edit', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> المحتوى العربي</h2>
                    </div>
                    <div class="ap-card__body">
                        <h3 style="font-size: 14px;">العربية</h3>
                        <div class="ap-field">
                            <label class="ap-label">عنوان العقار <span>*</span></label>
                            <input type="text" name="translations[ar][name]" class="ap-input" required
                                placeholder="مثال: شقة للبيع 140م تشطيب سوبر لوكس بالحي الأول"
                                value="<?php echo e(old('translations.ar.name', $ar->name)); ?>">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">الوصف التفصيلي للعقار</label>
                            <textarea name="translations[ar][description]" class="ap-textarea" rows="5"
                                placeholder="اكتب وصفاً مفصلاً وشاملاً للعقار ومميزاته ومواصفاته..."><?php echo e(old('translations.ar.description', $ar->description)); ?></textarea>
                            <small style="color: var(--ap-muted); font-size: 12px; display: block; margin-top: 5px;">
                                💡 يتم توليد الرابط اللطيف (Slug) واستنتاج الوصف المختصر تلقائياً وبدقة من العنوان والوصف التفصيلي.
                            </small>
                        </div>
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">العنوان التفصيلي</label>
                                <input type="text" name="translations[ar][address_text]" class="ap-input"
                                    value="<?php echo e(old('translations.ar.address_text', $ar->address_text)); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">عنوان SEO</label>
                                <input type="text" name="translations[ar][seo_title]" class="ap-input"
                                    value="<?php echo e(old('translations.ar.seo_title', $ar->seo_title)); ?>">
                            </div>
                        </div>

                        <?php if(config('locales.admin_english_enabled')): ?>
                        <hr style="margin: 18px 0; border: 0; border-top: 1px solid var(--ap-border);">

                        <h3 style="font-size: 14px;">English</h3>
                        <div class="ap-field">
                            <label class="ap-label">Property Title (EN)</label>
                            <input type="text" name="translations[en][name]" class="ap-input"
                                placeholder="e.g. Luxury Apartment 140m for Sale"
                                value="<?php echo e(old('translations.en.name', $en->name)); ?>">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Full description (EN)</label>
                            <textarea name="translations[en][description]" class="ap-textarea" rows="4"><?php echo e(old('translations.en.description', $en->description)); ?></textarea>
                        </div>
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">Address (EN)</label>
                                <input type="text" name="translations[en][address_text]" class="ap-input"
                                    value="<?php echo e(old('translations.en.address_text', $en->address_text)); ?>">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">SEO title (EN)</label>
                                <input type="text" name="translations[en][seo_title]" class="ap-input"
                                    value="<?php echo e(old('translations.en.seo_title', $en->seo_title)); ?>">
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ap-card border-primary border-opacity-25 bg-primary bg-opacity-10 mb-3">
                    <div class="ap-card__body py-3">
                        <label class="d-flex align-items-center gap-3 cursor-pointer m-0" style="cursor: pointer;">
                            <input type="checkbox" name="send_broadcast_notification" value="1" class="form-check-input mt-0 fs-5" id="propSendBroadcastNotif">
                            <div>
                                <strong class="d-block text-dark" style="font-size: 14px;">
                                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'bell', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    إرسال إشعار فوري لجميع المستخدمين والمشتركين
                                </strong>
                                <span class="text-muted small">عند التحديد، سيتم بث إشعار فوري (Web Push وفي الموقع) بعنوان وصورة هذا العقار ورابط مباشر إليه.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__body d-flex gap-2 flex-wrap">
                        <button class="ap-btn ap-btn--gold">
                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'check', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php echo e($isEdit ? 'حفظ التعديلات' : 'إنشاء العقار'); ?>

                        </button>
                        <a href="<?php echo e(admin_route('admin.properties.index')); ?>" class="ap-btn ap-btn--ghost">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    
    <?php if($isEdit): ?>
        <?php $__currentLoopData = $property->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!$media->is_primary): ?>
                <form id="mediaPrimary<?php echo e($media->id); ?>" method="post"
                    action="<?php echo e(admin_route('admin.properties.media.primary', [$property, $media])); ?>">
                    <?php echo csrf_field(); ?>
                </form>
            <?php endif; ?>
            <form id="mediaDelete<?php echo e($media->id); ?>" method="post"
                action="<?php echo e(admin_route('admin.properties.media.destroy', [$property, $media])); ?>"
                data-confirm="حذف هذه الصورة نهائياً؟">
                <?php echo csrf_field(); ?>
                <?php echo method_field('delete'); ?>
            </form>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var priceTypeSelect = document.getElementById('propertyPriceTypeSelect');
            var priceInput = document.getElementById('propertyPriceInput');
            var priceAsterisk = document.getElementById('propertyPriceRequiredAsterisk');
            var priceHint = document.getElementById('propertyPriceHint');

            function syncPriceState() {
                if (!priceTypeSelect || !priceInput) return;
                var val = priceTypeSelect.value;
                if (val === 'contact') {
                    priceInput.removeAttribute('required');
                    if (priceAsterisk) priceAsterisk.style.display = 'none';
                    if (priceHint) {
                        priceHint.textContent = 'عند اختيار "يتم تحديده من خلال التواصل"، سيظهر للزوار "السعر عند التواصل" ولن يظهر أي رقم.';
                        priceHint.style.color = '#d97706';
                    }
                } else if (val === 'negotiable') {
                    priceInput.setAttribute('required', 'required');
                    if (priceAsterisk) priceAsterisk.style.display = 'inline';
                    if (priceHint) {
                        priceHint.textContent = 'سيظهر السعر للزوار ومعه شارة مميزة "قابل للتفاوض".';
                        priceHint.style.color = '#059669';
                    }
                } else {
                    priceInput.setAttribute('required', 'required');
                    if (priceAsterisk) priceAsterisk.style.display = 'inline';
                    if (priceHint) {
                        priceHint.textContent = 'سعر محدد وثابت لا يقبل التفاوض.';
                        priceHint.style.color = '#64748b';
                    }
                }
            }

            if (priceTypeSelect) {
                priceTypeSelect.addEventListener('change', syncPriceState);
                syncPriceState();
            }

            // إدارة صفوف التخطيط الهندسي ديناميكياً
            var floorPlansContainer = document.getElementById('floorPlansContainer');
            var addFloorPlanBtn = document.getElementById('addFloorPlanBtn');
            var planCount = document.querySelectorAll('.floor-plan-row').length;

            if (addFloorPlanBtn && floorPlansContainer) {
                addFloorPlanBtn.addEventListener('click', function () {
                    var newIdx = planCount++;
                    var div = document.createElement('div');
                    div.className = 'floor-plan-row';
                    div.setAttribute('data-plan-index', newIdx);
                    div.style.cssText = 'background: rgba(248, 250, 252, 0.8); border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;';
                    div.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="font-size: 13px; color: #334155;" class="plan-number-label">📐 مخطط هندسي #${floorPlansContainer.children.length + 1}</strong>
                            <button type="button" class="ap-btn ap-btn--danger ap-btn--sm remove-floor-plan-btn" style="padding: 2px 8px; font-size: 11px;">
                                حذف
                            </button>
                        </div>
                        <div class="ap-grid ap-grid--2" style="margin-bottom: 10px;">
                            <div class="ap-field">
                                <label class="ap-label">اسم / عنوان المخطط</label>
                                <input type="text" name="floor_plans[${newIdx}][name]" class="ap-input" placeholder="مثال: مخطط الدور العلوي">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">المساحة (م²)</label>
                                <input type="number" step="0.1" min="0" name="floor_plans[${newIdx}][area]" class="ap-input" placeholder="140">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">غرف النوم</label>
                                <input type="number" min="0" name="floor_plans[${newIdx}][bedrooms]" class="ap-input" placeholder="3">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الحمامات</label>
                                <input type="number" min="0" name="floor_plans[${newIdx}][bathrooms]" class="ap-input" placeholder="2">
                            </div>
                        </div>
                        <div class="ap-field" style="margin-bottom: 10px;">
                            <label class="ap-label">صورة التخطيط الهندسي</label>
                            <input type="file" name="floor_plans[${newIdx}][image]" class="ap-input" accept="image/*">
                        </div>
                        <div class="ap-field" style="margin-bottom: 0;">
                            <label class="ap-label">ملاحظات أو مواصفات المخطط</label>
                            <textarea name="floor_plans[${newIdx}][description]" class="ap-textarea" rows="2" placeholder="تفاصيل إضافية عن التقسيم الداخلي والأبعاد..."></textarea>
                        </div>
                    `;
                    floorPlansContainer.appendChild(div);
                });

                floorPlansContainer.addEventListener('click', function (e) {
                    if (e.target && e.target.closest('.remove-floor-plan-btn')) {
                        var row = e.target.closest('.floor-plan-row');
                        if (row) {
                            row.remove();
                            var labels = floorPlansContainer.querySelectorAll('.plan-number-label');
                            labels.forEach(function (lbl, i) {
                                lbl.textContent = '📐 مخطط هندسي #' + (i + 1);
                            });
                        }
                    }
                });
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/properties/form.blade.php ENDPATH**/ ?>