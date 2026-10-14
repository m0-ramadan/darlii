<?php
    $isEnabled = setting('firebase.enabled', '1') == '1';
?>
<?php if($isEnabled): ?>
<div id="darli-notifications-wrapper" class="darli-notifications-widget" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
    
    <button type="button" id="darli-notif-toggle-btn" class="darli-notif-toggle-btn" aria-label="الإشعارات" title="الإشعارات">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/>
            <path d="M9 17v1a3 3 0 0 0 6 0v-1"/>
        </svg>
        <span class="darli-notif-badge" style="display: none;">0</span>
    </button>

    
    <div id="darli-notif-panel" class="darli-notif-panel" style="display: none;">
        <div class="darli-notif-panel__header">
            <div class="d-flex align-items-center gap-2">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#E32C2B" stroke-width="2"><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>
                <span class="fw-bold fs-6 text-dark">الإشعارات</span>
            </div>
            <button type="button" class="darli-mark-all-read-btn btn btn-sm btn-link text-decoration-none p-0 text-muted" style="font-size: 11.5px;">
                تحديد الكل كمقروء
            </button>
        </div>

        <div class="darli-notif-dropdown-list" style="max-height: 380px; overflow-y: auto;">
            <div class="p-4 text-center text-muted" style="font-size: 12px;">جاري تحميل الإشعارات...</div>
        </div>
    </div>
</div>

<style>
.darli-notifications-widget {
    position: fixed;
    bottom: 96px;
    right: 28px;
    z-index: 99990;
    font-family: 'Almarai', sans-serif;
}
[dir="ltr"] .darli-notifications-widget {
    right: auto;
    left: 28px;
}
.darli-notif-toggle-btn {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #ffffff;
    color: #1e293b;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease, background 0.2s ease;
}
.darli-notif-toggle-btn:hover {
    transform: scale(1.08);
    box-shadow: 0 8px 24px rgba(227, 44, 43, 0.2);
    color: #E32C2B;
}
.darli-notif-badge {
    position: absolute;
    top: -2px;
    left: -2px;
    background: #E32C2B;
    color: #ffffff;
    font-size: 10.5px;
    font-weight: 700;
    min-width: 19px;
    height: 19px;
    border-radius: 10px;
    padding: 0 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
}
.darli-notif-panel {
    position: absolute;
    bottom: 64px;
    right: 0;
    width: 360px;
    max-width: calc(100vw - 32px);
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.18);
    border: 1px solid rgba(0,0,0,0.08);
    overflow: hidden;
    animation: darliFadeUp 0.25s ease forwards;
}
[dir="ltr"] .darli-notif-panel {
    right: auto;
    left: 0;
}
.darli-notif-panel__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    background: #fdfdfd;
}
@keyframes darliFadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes darliFadeDown {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
}
@media (max-width: 576px) {
    .darli-notifications-widget {
        bottom: 86px;
        right: 18px;
    }
    [dir="ltr"] .darli-notifications-widget {
        right: auto;
        left: 18px;
    }
    .darli-notif-panel {
        width: 310px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('darli-notif-toggle-btn');
    const panel = document.getElementById('darli-notif-panel');

    if (toggleBtn && panel) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = panel.style.display !== 'none';
            panel.style.display = isOpen ? 'none' : 'block';
        });

        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && e.target !== toggleBtn) {
                panel.style.display = 'none';
            }
        });
    }
});
</script>
<?php endif; ?>

<?php /**PATH /var/www/html/resources/views/components/floating-notifications.blade.php ENDPATH**/ ?>