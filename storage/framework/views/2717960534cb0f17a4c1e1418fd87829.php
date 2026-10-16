<?php if($paginator->hasPages()): ?>
    <?php $locale = app()->getLocale(); ?>
    <nav role="navigation" aria-label="<?php echo e($locale === 'ar' ? 'التنقل بين الصفحات' : 'Pagination Navigation'); ?>"
         class="ap-pagination-bar">
        
        <ul class="pagination">
            
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled">
                    <span class="page-link"><?php echo e($locale === 'ar' ? '‹' : '›'); ?></span>
                </li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev"
                       aria-label="<?php echo e($locale === 'ar' ? 'السابق' : 'Previous'); ?>"><?php echo e($locale === 'ar' ? '‹' : '›'); ?></a>
                </li>
            <?php endif; ?>

            
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_string($element)): ?>
                    <li class="page-item disabled"><span class="page-link"><?php echo e($element); ?></span></li>
                <?php endif; ?>
                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li class="page-item active" aria-current="page">
                                <span class="page-link"><?php echo e($page); ?></span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next"
                       aria-label="<?php echo e($locale === 'ar' ? 'التالي' : 'Next'); ?>"><?php echo e($locale === 'ar' ? '›' : '‹'); ?></a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link"><?php echo e($locale === 'ar' ? '›' : '‹'); ?></span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/vendor/pagination/darli.blade.php ENDPATH**/ ?>