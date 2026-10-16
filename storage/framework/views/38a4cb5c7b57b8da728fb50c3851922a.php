<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $filters = request()->only(['q', 'k', 'purpose', 'category_id', 'location_id', 'sort']);
    $searchTerm = $filters['q'] ?? ($filters['k'] ?? '');
?>

<?php $__env->startSection('content'); ?>
    
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/plugins/leaflet/leaflet.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/darli-properties-map.css')); ?>?v=1.0.4">

    <div class="darli-properties-page">
        <div class="darli-properties-split-container">

            
            <div class="darli-split-content-pane">

                
                <div class="darli-prop-header">
                    <div class="darli-prop-title-box">
                        <div class="darli-prop-breadcrumb">
                            <a href="<?php echo e(url($locale)); ?>"><?php echo e($isAr ? 'الرئيسية' : 'Home'); ?></a>
                            <span class="sep">›</span>
                            <span><?php echo e($isAr ? 'العقارات' : 'Properties'); ?></span>
                        </div>
                        <h1 class="darli-prop-title">
                            <?php echo e($isAr ? 'العقارات في ' . ($currentCity ?? 'بني سويف') : 'Properties in ' . ($currentCity ?? 'Beni Suef')); ?>

                        </h1>
                        <p class="darli-prop-subtitle">
                            <?php echo e($isAr ? 'اكتشف أفضل العقارات المتاحة للبيع والإيجار في جميع مناطق بني سويف' : 'Discover the best properties available for sale and rent in all areas of Beni Suef'); ?>

                        </p>
                    </div>

                    <div class="darli-city-badge-card">
                        <div class="darli-city-badge-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                            </svg>
                        </div>
                        <div class="darli-city-badge-info">
                            <span
                                class="darli-city-badge-name"><?php echo e($currentCity ?? ($isAr ? 'بني سويف' : 'Beni Suef')); ?></span>
                            <span class="darli-city-badge-count"><?php echo e(number_format($properties->total())); ?>

                                <?php echo e($isAr ? 'عقار متاح' : 'properties'); ?></span>
                        </div>
                    </div>
                </div>

                
                <div class="darli-prop-filter-card">
                    <form id="darli-filter-form" method="get"
                        action="<?php echo e(route('properties.index', ['locale' => $locale])); ?>" class="darli-filter-form">
                        <input type="hidden" name="sort" value="<?php echo e(request('sort', 'newest')); ?>">

                        
                        <div class="darli-filter-field search-field">
                            <label class="darli-filter-label"><?php echo e($isAr ? 'البحث' : 'Search'); ?></label>
                            <div class="darli-input-icon-wrap">
                                <input type="search" name="q" value="<?php echo e($searchTerm); ?>" class="darli-filter-input"
                                    placeholder="<?php echo e($isAr ? 'اسم العقار أو الكود...' : 'Property name or code...'); ?>">
                                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                        </div>

                        
                        <div class="darli-filter-field">
                            <label class="darli-filter-label"><?php echo e($isAr ? 'الغرض' : 'Purpose'); ?></label>
                            <select name="purpose" class="darli-filter-select">
                                <option value=""><?php echo e($isAr ? 'الكل' : 'All'); ?></option>
                                <option value="sale" <?php if(($filters['purpose'] ?? '') === 'sale'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'للبيع' : 'For sale'); ?>

                                </option>
                                <option value="rent" <?php if(($filters['purpose'] ?? '') === 'rent'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'للإيجار' : 'For rent'); ?>

                                </option>
                            </select>
                        </div>

                        
                        <div class="darli-filter-field">
                            <label class="darli-filter-label"><?php echo e($isAr ? 'النوع' : 'Type'); ?></label>
                            <select name="category_id" class="darli-filter-select">
                                <option value=""><?php echo e($isAr ? 'الكل' : 'All'); ?></option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>" <?php if((string) ($filters['category_id'] ?? '') === (string) $category->id): echo 'selected'; endif; ?>>
                                        <?php echo e($category->translation($locale)?->name ?? $category->key); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        
                        <div class="darli-filter-field">
                            <label class="darli-filter-label"><?php echo e($isAr ? 'المنطقة' : 'Area'); ?></label>
                            <select name="location_id" class="darli-filter-select">
                                <option value=""><?php echo e($isAr ? 'كل المناطق' : 'All areas'); ?></option>
                                <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($location->id); ?>" <?php if((string) ($filters['location_id'] ?? '') === (string) $location->id): echo 'selected'; endif; ?>>
                                        <?php echo e($location->translation($locale)?->name ?? $location->id); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        
                        <div class="darli-filter-actions">
                            <a href="<?php echo e(route('properties.index', ['locale' => $locale])); ?>"
                                class="darli-filter-btn-reset">
                                <?php echo e($isAr ? 'إعادة تعيين' : 'Reset'); ?>

                            </a>
                            <button type="submit" class="darli-filter-btn-submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <span><?php echo e($isAr ? 'بحث عن العقارات' : 'Search properties'); ?></span>
                            </button>
                        </div>
                    </form>
                </div>

                
                <div class="darli-prop-subbar">
                    <div class="darli-prop-count-badge">
                        <?php echo e(number_format($properties->total())); ?> <?php echo e($isAr ? 'عقار متاح' : 'properties available'); ?>

                    </div>

                    <div class="darli-prop-controls">
                        <div class="darli-view-toggles">
                            <button type="button" id="darli-view-grid" class="darli-view-btn active"
                                title="<?php echo e($isAr ? 'عرض شبكي' : 'Grid view'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                                    <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                                </svg>
                            </button>
                            <button type="button" id="darli-view-list" class="darli-view-btn"
                                title="<?php echo e($isAr ? 'عرض طولي' : 'List view'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </button>
                        </div>

                        <select id="darli-sort-select" class="darli-sort-select">
                            <option value="newest" <?php if(request('sort', 'newest') === 'newest'): echo 'selected'; endif; ?>>
                                <?php echo e($isAr ? 'أحدث أولاً' : 'Newest first'); ?></option>
                            <option value="price_asc" <?php if(request('sort') === 'price_asc'): echo 'selected'; endif; ?>>
                                <?php echo e($isAr ? 'السعر: من الأقل للأعلى' : 'Price: Low to High'); ?></option>
                            <option value="price_desc" <?php if(request('sort') === 'price_desc'): echo 'selected'; endif; ?>>
                                <?php echo e($isAr ? 'السعر: من الأعلى للأقل' : 'Price: High to Low'); ?></option>
                            <option value="popular" <?php if(request('sort') === 'popular'): echo 'selected'; endif; ?>>
                                <?php echo e($isAr ? 'الأكثر شعبية' : 'Most popular'); ?></option>
                        </select>
                    </div>
                </div>

                
                <?php if($properties->isEmpty()): ?>
                    <div class="darli-panel darli-empty p-5 text-center"
                        style="background:#fff;border-radius:12px;border:1px solid #edf2f7;">
                        <h4 class="mb-2" style="font-weight:700;">
                            <?php echo e($isAr ? 'لا توجد عقارات مطابقة للبحث' : 'No matching properties found'); ?></h4>
                        <p class="text-muted mb-0">
                            <?php echo e($isAr ? 'جرّب تغيير خيارات الفلترة أو إعادة تعيين البحث لعرض كافة العقارات.' : 'Try changing your filter criteria or reset to view all available properties.'); ?>

                        </p>
                    </div>
                <?php else: ?>
                    <div class="darli-props-grid">
                        <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $tr = $property->translation($locale);
                                $isRent = $property->purpose === 'rent';
                                $slug = $tr?->slug;
                                $url = $slug ? route('properties.show', ['locale' => $locale, 'slug' => $slug]) : '#';
                                $image = $property->primaryImageUrl($locale);
                                $purposeLabel = $isRent
                                    ? ($isAr
                                        ? 'للإيجار'
                                        : 'For rent')
                                    : ($isAr
                                        ? 'للبيع'
                                        : 'For sale');
                                $purposeClass = $isRent ? 'rent' : 'sale';
                                $categoryName = $property->category?->translation($locale)?->name;
                                $locationName =
                                    $tr?->address_text ?:
                                    ($property->location?->translation($locale)?->name ??
                                        ($isAr ? 'بني سويف' : 'Beni Suef'));
                                $isContactPrice = $property->isPriceOnContact();
                                $isNegotiablePrice = $property->isNegotiable();
                                $formattedPrice = $isContactPrice
                                    ? ($isAr ? 'السعر عند التواصل' : 'Price on request')
                                    : (number_format((float) $property->price, 0) . ' ' . ($isAr ? 'جنيه' : 'EGP'));
                                $areaFormatted = $property->area
                                    ? rtrim(rtrim(number_format((float) $property->area, 2), '0'), '.')
                                    : null;
                            ?>

                            <div class="darli-prop-card" data-id="<?php echo e($property->id); ?>"
                                data-purpose="<?php echo e($property->purpose); ?>" data-lat="<?php echo e($property->latitude); ?>"
                                data-lng="<?php echo e($property->longitude); ?>">
                                
                                <div class="darli-prop-card-media">
                                    <a href="<?php echo e($url); ?>">
                                        <img src="<?php echo e($image); ?>" alt="<?php echo e($tr?->name); ?>" loading="lazy"
                                            onerror="this.onerror=null;this.src='<?php echo e($property->placeholderUrl($locale)); ?>';">
                                    </a>

                                    
                                    <div class="darli-badges-top-left">
                                        <?php if($property->isSold()): ?>
                                            <span class="darli-badge-sold"><?php echo e($isAr ? 'تم البيع' : 'Sold'); ?></span>
                                        <?php endif; ?>
                                        <span class="darli-badge-purpose <?php echo e($purposeClass); ?>"><?php echo e($purposeLabel); ?></span>
                                    </div>

                                    
                                    <button type="button" class="darli-badge-wishlist" data-id="<?php echo e($property->id); ?>"
                                        title="<?php echo e($isAr ? 'إضافة إلى المفضلة' : 'Add to wishlist'); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                        </svg>
                                    </button>

                                    
                                    <?php if($categoryName): ?>
                                        <span class="darli-badge-category"><?php echo e($categoryName); ?></span>
                                    <?php endif; ?>
                                </div>

                                
                                <div class="darli-prop-card-body">
                                    
                                    <a href="<?php echo e($url); ?>" class="darli-prop-card-title"
                                        title="<?php echo e($tr?->name); ?>">
                                        <?php echo e($tr?->name ?? $property->reference_code); ?>

                                    </a>

                                    
                                    <div class="darli-prop-card-loc">
                                        <svg class="darli-prop-loc-pin" xmlns="http://www.w3.org/2000/svg" width="14"
                                            height="14" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                                        </svg>
                                        <span><?php echo e($locationName); ?></span>
                                    </div>

                                    
                                    <div class="darli-prop-card-price d-flex align-items-center gap-2 flex-wrap">
                                        <span><?php echo e($formattedPrice); ?></span>
                                        <?php if($isRent && ! $isContactPrice): ?>
                                            <span class="rent-period"><?php echo e($isAr ? ' / شهرياً' : ' / mo'); ?></span>
                                        <?php endif; ?>
                                        <?php if($isNegotiablePrice): ?>
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                                                <?php echo e($isAr ? 'قابل للتفاوض' : 'Negotiable'); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="darli-prop-card-divider"></div>

                                    
                                    <div class="darli-prop-card-meta">
                                        
                                        <div class="darli-prop-meta-item" title="<?php echo e($isAr ? 'المساحة' : 'Area'); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path
                                                    d="M21.3 8.7 8.7 21.3c-1 1-2.5 1-3.4 0l-2.6-2.6c-1-1-1-2.5 0-3.4L15.3 2.7c1-1 2.5-1 3.4 0l2.6 2.6c1 1 1 2.5 0 3.4Z" />
                                                <path d="m7.5 10.5 2 2" />
                                                <path d="m10.5 7.5 2 2" />
                                            </svg>
                                            <span><?php echo e($areaFormatted ? $areaFormatted . ($isAr ? ' م²' : ' m²') : '—'); ?></span>
                                        </div>

                                        
                                        <div class="darli-prop-meta-item" title="<?php echo e($isAr ? 'الغرف' : 'Bedrooms'); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 4v16" />
                                                <path d="M2 8h18a2 2 0 0 1 2 2v10" />
                                                <path d="M2 17h20" />
                                                <path d="M6 8v9" />
                                            </svg>
                                            <span>
                                                <?php if($property->bedrooms > 0): ?>
                                                    <?php echo e($property->bedrooms); ?> <?php echo e($isAr ? 'حجر' : 'rooms'); ?>

                                                <?php else: ?>
                                                    <?php echo e($isAr ? 'طابق أرضي' : 'Ground floor'); ?>

                                                <?php endif; ?>
                                            </span>
                                        </div>

                                        
                                        <div class="darli-prop-meta-item" title="<?php echo e($isAr ? 'الحمامات' : 'Bathrooms'); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-1.5C4 2 2 3.5 2 5.5V11" />
                                                <path d="M2 11h20v2a7 7 0 0 1-7 7H9a7 7 0 0 1-7-7v-2Z" />
                                                <path d="M7 20v2" />
                                                <path d="M17 20v2" />
                                            </svg>
                                            <span><?php echo e($property->bathrooms ?? 1); ?> <?php echo e($isAr ? 'حمام' : 'bath'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    
                    <?php if($properties->hasPages()): ?>
                        <div class="darli-pagination d-flex justify-content-center mt-4">
                            <?php echo e($properties->links('vendor.pagination.darli')); ?>

                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            
            <div class="darli-split-map-pane">

                
                <div class="darli-map-purpose-card">
                    <div class="darli-map-purpose-item rent" data-purpose="rent"
                        title="<?php echo e($isAr ? 'تصفية عقارات للإيجار' : 'Filter rent'); ?>">
                        <div class="darli-map-purpose-icon rent">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                            </svg>
                        </div>
                        <div class="darli-map-purpose-text">
                            <span class="darli-map-purpose-title"><?php echo e($isAr ? 'للإيجار' : 'For rent'); ?></span>
                            <span
                                class="darli-map-purpose-sub"><?php echo e($isAr ? 'عقارات للإيجار' : 'Properties for rent'); ?></span>
                        </div>
                    </div>

                    <div class="darli-map-purpose-divider"></div>

                    <div class="darli-map-purpose-item sale" data-purpose="sale"
                        title="<?php echo e($isAr ? 'تصفية عقارات للبيع' : 'Filter sale'); ?>">
                        <div class="darli-map-purpose-icon sale">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                            </svg>
                        </div>
                        <div class="darli-map-purpose-text">
                            <span class="darli-map-purpose-title"><?php echo e($isAr ? 'للبيع' : 'For sale'); ?></span>
                            <span
                                class="darli-map-purpose-sub"><?php echo e($isAr ? 'منازل ومسكنات للبيع' : 'Houses for sale'); ?></span>
                        </div>
                    </div>
                </div>

                
                <div id="darli-properties-map"></div>

                
                <div class="darli-map-btn-type-wrap">
                    <button type="button" id="darli-map-btn-type" class="darli-map-btn-type">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                        <span><?php echo e($isAr ? 'نوع الخريطة' : 'Map type'); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div id="darli-map-layer-menu" class="darli-map-layer-menu">
                        <div class="darli-map-layer-item" data-layer="light">
                            <?php echo e($isAr ? 'خريطة شوارع' : 'Street map'); ?></div>
                        <div class="darli-map-layer-item active" data-layer="satellite"><?php echo e($isAr ? 'قمر صناعي' : 'Satellite'); ?>

                        </div>
                        <div class="darli-map-layer-item" data-layer="dark"><?php echo e($isAr ? 'الوضع الليلي' : 'Dark mode'); ?>

                        </div>
                    </div>
                </div>

                
                <button type="button" id="darli-map-btn-area" class="darli-map-btn-area">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                        fill="currentColor" style="color: var(--darli-gold);">
                        <path
                            d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                    </svg>
                    <span><?php echo e($isAr ? 'عرض العقارات في هذه المنطقة' : 'Show properties in this area'); ?></span>
                </button>

            </div>
        </div>
    </div>

    
    <script>
        window.__DARLI_MAP_PROPERTIES__ = <?php echo json_encode($mapProperties, 15, 512) ?>;
    </script>
    <script src="<?php echo e(asset('themes/homzen/plugins/leaflet/leaflet.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/homzen/js/darli-properties-map.js')); ?>?v=1.0.5"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/properties/index.blade.php ENDPATH**/ ?>