<?php $__env->startSection('title', __('messages.blog')); ?>

<?php $__env->startPush('head'); ?>
    <style>
        .darli-pagination-wrap {
            align-items: center;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 32px;
        }

        .darli-pagination {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin: 0;
            padding: 0;
        }

        .darli-page-item {
            list-style: none;
        }

        .darli-page-link {
            align-items: center;
            background: #fff;
            border: 1px solid #dfe3ea;
            border-radius: 8px;
            color: #161e2d;
            display: inline-flex;
            font-size: 14px;
            font-weight: 600;
            justify-content: center;
            min-height: 40px;
            min-width: 42px;
            padding: 8px 14px;
            text-decoration: none;
            transition: .2s;
        }

        .darli-page-link:hover {
            border-color: #1563df;
            color: #1563df;
        }

        .darli-page-item.active .darli-page-link {
            background: #1563df;
            border-color: #1563df;
            color: #fff;
        }

        .darli-page-item.disabled .darli-page-link {
            background: #f6f7f9;
            color: #98a1b2;
            cursor: not-allowed;
            pointer-events: none;
        }

        .darli-pagination-summary {
            color: #6b7280;
            font-size: 14px;
            text-align: center;
        }

        html[data-theme="dark"] .darli-page-link {
            background: #171c26;
            border-color: #2f3747;
            color: #e5e7eb;
        }

        html[data-theme="dark"] .darli-page-link:hover {
            background: #1f2633;
            border-color: #5b7cff;
            color: #ffffff;
        }

        html[data-theme="dark"] .darli-page-item.active .darli-page-link {
            background: #4f46e5;
            border-color: #6d64ff;
            color: #ffffff;
        }

        html[data-theme="dark"] .darli-page-item.disabled .darli-page-link {
            background: #11151d;
            border-color: #252c3a;
            color: #6b7280;
        }

        html[data-theme="dark"] .darli-pagination-summary {
            color: #9ca3af;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container page-section">
        <h1 class="mb-4"><?php echo e(__('messages.blog')); ?></h1>

        <div class="row g-4">
            <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($tr = $p->translation()); ?>
                <div class="col-md-6 col-lg-4">
                    <article class="border rounded-4 p-4 h-100">
                        <h4>
                            <a href="<?php echo e(route('blog.show', ['locale' => app()->getLocale(), 'slug' => $tr?->slug])); ?>">
                                <?php echo e($tr?->title); ?>

                            </a>
                        </h4>
                        <p class="text-muted"><?php echo e($tr?->excerpt); ?></p>
                    </article>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($posts->hasPages()): ?>
            <nav class="darli-pagination-wrap" aria-label="<?php echo e(__('messages.blog')); ?>">
                <ul class="darli-pagination">
                    <li class="darli-page-item <?php if($posts->onFirstPage()): ?> disabled <?php endif; ?>">
                        <?php if($posts->onFirstPage()): ?>
                            <span class="darli-page-link"><?php echo e(app()->getLocale() === 'ar' ? 'السابق' : 'Previous'); ?></span>
                        <?php else: ?>
                            <a class="darli-page-link" href="<?php echo e($posts->previousPageUrl()); ?>" rel="prev">
                                <?php echo e(app()->getLocale() === 'ar' ? 'السابق' : 'Previous'); ?>

                            </a>
                        <?php endif; ?>
                    </li>

                    <?php $__currentLoopData = $posts->getUrlRange(1, $posts->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="darli-page-item <?php if($page === $posts->currentPage()): ?> active <?php endif; ?>">
                            <?php if($page === $posts->currentPage()): ?>
                                <span class="darli-page-link"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a class="darli-page-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <li class="darli-page-item <?php if (! ($posts->hasMorePages())): ?> disabled <?php endif; ?>">
                        <?php if($posts->hasMorePages()): ?>
                            <a class="darli-page-link" href="<?php echo e($posts->nextPageUrl()); ?>" rel="next">
                                <?php echo e(app()->getLocale() === 'ar' ? 'التالي' : 'Next'); ?>

                            </a>
                        <?php else: ?>
                            <span class="darli-page-link"><?php echo e(app()->getLocale() === 'ar' ? 'التالي' : 'Next'); ?></span>
                        <?php endif; ?>
                    </li>
                </ul>

                <div class="darli-pagination-summary">
                    <?php echo e(app()->getLocale() === 'ar'
                        ? 'عرض ' . $posts->firstItem() . ' إلى ' . $posts->lastItem() . ' من ' . $posts->total() . ' نتيجة'
                        : 'Showing ' . $posts->firstItem() . ' to ' . $posts->lastItem() . ' of ' . $posts->total() . ' results'); ?>

                </div>
            </nav>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/blog/index.blade.php ENDPATH**/ ?>