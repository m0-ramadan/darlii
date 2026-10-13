<?php
    $user = auth()->user();
?>

<div class="ap-tabs">
    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($tab['permission'] && $user && ! $user->hasPermission($tab['permission'])) continue; ?>
        <a href="<?php echo e(admin_route($tab['route'])); ?>" class="ap-tab <?php if($active === $key): ?> is-active <?php endif; ?>">
            <?php echo $__env->make('admin.partials.icon', ['iconName' => $tab['icon'], 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo e($tab['label']); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH /var/www/html/resources/views/admin/settings/_tabs.blade.php ENDPATH**/ ?>