<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('page_title', $title); ?>
<?php $__env->startSection('page_subtitle', $subtitle ?? ''); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('admin.settings._tabs', ['tabs' => $tabs, 'active' => $active], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form method="post" action="<?php echo e(admin_route('admin.settings.update', ['group' => $group])); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php echo method_field('put'); ?>

        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'settings', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php echo e($title); ?>

                </h2>
                <span class="ap-badge"><?php echo e($fields->count()); ?> إعداد</span>
            </div>

            <div class="ap-card__body">
                <?php if($fields->isEmpty()): ?>
                    <div class="ap-empty">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'alert', 'size' => 40], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <p>لا توجد إعدادات مخزنة لهذه المجموعة بعد.</p>
                    </div>
                <?php else: ?>
                    <div class="ap-grid ap-grid--2">
                        <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $value = old($field->key, $field->value);
                                $isWide = in_array($field->type, ['textarea', 'image'], true);
                                // PHP converts dots in form field names to underscores,
                                // and Laravel additionally treats "a.b" as a nested key.
                                // So the input name uses underscores and we map it back below.
                                $inputName = str_replace('.', '_', $field->key);
                            ?>

                            <div class="ap-field" <?php if($isWide): ?> style="grid-column: 1 / -1;" <?php endif; ?>>
                                <label class="ap-label" for="field-<?php echo e($inputName); ?>">
                                    <?php echo e($field->label_ar ?: $field->key); ?>

                                </label>

                                <?php switch($field->type):
                                    case ('textarea'): ?>
                                        <textarea id="field-<?php echo e($inputName); ?>" name="<?php echo e($inputName); ?>" class="ap-textarea" rows="4"><?php echo e($value); ?></textarea>
                                        <?php break; ?>

                                    <?php case ('boolean'): ?>
                                        <input type="hidden" name="<?php echo e($inputName); ?>" value="0">
                                        <label class="ap-switch">
                                            <input type="checkbox" id="field-<?php echo e($inputName); ?>" name="<?php echo e($inputName); ?>"
                                                value="1" <?php if((bool) $value): echo 'checked'; endif; ?>>
                                            <span class="ap-switch__track"></span>
                                            <span><?php echo e((bool) $value ? 'مُفعّل' : 'مُعطّل'); ?></span>
                                        </label>
                                        <?php break; ?>

                                    <?php case ('password'): ?>
                                    <?php case ('secret'): ?>
                                        <input type="password" id="field-<?php echo e($inputName); ?>" name="<?php echo e($inputName); ?>"
                                            class="ap-input" value="" autocomplete="new-password"
                                            placeholder="<?php echo e($value ? '•••••••• (محفوظ — اتركه فارغاً للإبقاء عليه)' : 'أدخل المفتاح'); ?>">
                                        <?php break; ?>

                                    <?php case ('number'): ?>
                                        <input type="number" id="field-<?php echo e($inputName); ?>" name="<?php echo e($inputName); ?>"
                                            class="ap-input" value="<?php echo e($value); ?>">
                                        <?php break; ?>

                                    <?php case ('color'): ?>
                                        
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="color" id="swatch-<?php echo e($inputName); ?>" value="<?php echo e($value ?: '#0F3D2E'); ?>"
                                                oninput="document.getElementById('field-<?php echo e($inputName); ?>').value = this.value"
                                                style="width: 54px; height: 40px; border: 1px solid var(--ap-border); border-radius: 8px; background: #fff; cursor: pointer;">
                                            <input type="text" id="field-<?php echo e($inputName); ?>" name="<?php echo e($inputName); ?>" class="ap-input"
                                                value="<?php echo e($value); ?>" placeholder="#0F3D2E"
                                                oninput="document.getElementById('swatch-<?php echo e($inputName); ?>').value = this.value">
                                        </div>
                                        <?php break; ?>

                                    <?php case ('image'): ?>
                                    <?php case ('file'): ?>
                                        <div class="d-flex gap-3 align-items-center flex-wrap">
                                            <?php if($value): ?>
                                                <img src="<?php echo e(str_starts_with((string) $value, 'http') ? $value : asset($value)); ?>"
                                                    alt="" style="height: 56px; background: #f4f6f8; border-radius: 8px; padding: 6px; border: 1px solid var(--ap-border);">
                                            <?php endif; ?>
                                            <input type="file" id="field-<?php echo e($inputName); ?>" name="<?php echo e($inputName); ?>"
                                                class="ap-input" accept="image/*">
                                        </div>
                                        <div class="ap-help">اتركه فارغاً للإبقاء على الملف الحالي: <code><?php echo e($value ?: '—'); ?></code></div>
                                        <?php break; ?>

                                    <?php default: ?>
                                        <input type="text" id="field-<?php echo e($inputName); ?>" name="<?php echo e($inputName); ?>"
                                            class="ap-input" value="<?php echo e($value); ?>">
                                <?php endswitch; ?>

                                <?php if($field->help_ar): ?>
                                    <div class="ap-help"><?php echo e($field->help_ar); ?></div>
                                <?php endif; ?>
                                <div class="ap-help" style="opacity: .6;">المفتاح: <code><?php echo e($field->key); ?></code></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($fields->isNotEmpty()): ?>
                <div class="ap-card__body" style="border-top: 1px solid var(--ap-border);">
                    <button class="ap-btn ap-btn--gold">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'check', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> حفظ الإعدادات
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/settings/form.blade.php ENDPATH**/ ?>