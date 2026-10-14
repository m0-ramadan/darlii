<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['property', 'locale' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['property', 'locale' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $locale = $locale ?: app()->getLocale();
    $tr = $property->translation($locale);
    $slug = $tr?->slug;
    $url = $slug ? route('properties.show', ['locale' => $locale, 'slug' => $slug]) : '#';
    $image = $property->primaryImageUrl($locale);
    $isContactPrice = $property->isPriceOnContact();
    $isNegotiablePrice = $property->isNegotiable();
    $price = $isContactPrice
        ? ($locale === 'ar' ? 'السعر عند التواصل' : 'Price on request')
        : money_eg($property->price);
    $isRent = $property->purpose === 'rent';
?>


<div class="property-item homeya-box" data-lat="<?php echo e($property->latitude); ?>" data-lng="<?php echo e($property->longitude); ?>">
    <div class="archive-top">
        <a href="<?php echo e($url); ?>" class="images-group">
            <div class="images-style">
                <img src="<?php echo e($image); ?>" alt="<?php echo e($tr?->name); ?>"
                    onerror="this.onerror=null;this.src='<?php echo e($property->placeholderUrl($locale)); ?>';">
            </div>
            <div class="top">
                <div class="d-flex gap-8">
                    <?php if($property->isSold()): ?>
                        <span class="flag-tag success status-badge-sold" style="background-color: #10b981 !important; color: #fff !important; font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: 12px; box-shadow: 0 2px 6px rgba(16,185,129,0.3);">
                            <?php echo e($locale === 'ar' ? 'تم البيع' : 'Sold'); ?>

                        </span>
                    <?php endif; ?>
                    <?php if($property->is_featured): ?>
                        <span class="flag-tag success"><?php echo e($locale === 'ar' ? 'مميز' : 'Featured'); ?></span>
                    <?php endif; ?>
                    <span class="flag-tag primary status-badge-renting">
                        <?php echo e($isRent ? ($locale === 'ar' ? 'للإيجار' : 'For rent') : ($locale === 'ar' ? 'للبيع' : 'For sale')); ?>

                    </span>
                </div>
            </div>
            <?php if($property->category): ?>
                <div class="bottom">
                    <span class="flag-tag style-2"><?php echo e($property->category->translation($locale)?->name); ?></span>
                </div>
            <?php endif; ?>
        </a>

        <div class="content">
            <div class="text-capitalize h7 fw-7">
                <a href="<?php echo e($url); ?>" class="link line-clamp-1" title="<?php echo e($tr?->name); ?>"><?php echo e($tr?->name); ?></a>
            </div>
            <div class="desc">
                <i class="icon icon-mapPin"></i>
                <p class="line-clamp-1"><?php echo e($tr?->address_text ?: ($property->location?->translation($locale)?->name ?? '')); ?></p>
            </div>
            <ul class="meta-list">
                <li class="item"><i class="icon icon-bed"></i><span><?php echo e($property->bedrooms ?? '—'); ?></span></li>
                <li class="item"><i class="icon icon-bathtub"></i><span><?php echo e($property->bathrooms ?? '—'); ?></span></li>
                <li class="item"><i class="icon icon-ruler"></i><span><?php echo e(rtrim(rtrim(number_format((float) $property->area, 2), '0'), '.')); ?> m²</span></li>
            </ul>
        </div>
    </div>

    <div class="archive-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex gap-8 align-items-center">
            <div class="avatar avt-40 round">
                <img src="<?php echo e($property->owner?->avatar_url ?? asset('themes/homzen/images/logo/darli-logo-ar.png')); ?>"
                    alt="<?php echo e($property->owner?->name); ?>"
                    onerror="this.onerror=null;this.src='<?php echo e(asset('themes/homzen/images/logo/darli-logo-ar.png')); ?>';">
            </div>
            <span><?php echo e($property->owner?->name ?? ($locale === 'ar' ? 'فريق دارلي' : 'Darlii team')); ?></span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h6><?php echo e($price); ?><?php echo e(($isRent && ! $isContactPrice) ? ($locale === 'ar' ? ' / شهرياً' : ' / month') : ''); ?></h6>
            <?php if($isNegotiablePrice): ?>
                <span class="flag-tag success" style="font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                    <?php echo e($locale === 'ar' ? 'قابل للتفاوض' : 'Negotiable'); ?>

                </span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/legacy-property-card.blade.php ENDPATH**/ ?>