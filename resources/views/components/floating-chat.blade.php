@php
    $isAr = app()->getLocale() === 'ar';
    $rawWhatsapp = (string) setting('contact.whatsapp', '+20 10 34905812');
    $whatsappDigits = preg_replace('/\D+/', '', $rawWhatsapp) ?? '';
    $phone = (string) setting('contact.phone', '+20 10 34905812');
    $phoneDigits = preg_replace('/[^\d+]/', '', $phone) ?? '';
    $email = trim((string) setting('contact.email', 'contact@darlii.eg'));

    $facebook = trim((string) setting('contact.facebook', ''));
    if (preg_match('~^https?://(?:www\.)?facebook\.com/?$~i', $facebook)) {
        $facebook = '';
    }

    $instagram = trim((string) setting('contact.instagram', ''));
    if (preg_match('~^https?://(?:www\.)?instagram\.com/?$~i', $instagram)) {
        $instagram = '';
    }

    $welcomeText = $isAr
        ? 'أهلاً بك في دارلي 👋 كيف نقدر نساعدك في البحث عن عقارك؟'
        : 'Welcome to Darli 👋 How can we help with your property search?';
    $chatIntro = $isAr
        ? 'أهلاً بك 👋 اكتب لنا ما تبحث عنه وسنساعدك في اختيار العقار المناسب.'
        : 'Welcome 👋 Tell us what you are looking for and we will help you find the right property.';
    $defaultMessage = $isAr
        ? 'مرحباً، أود الاستفسار عن عقار في دارلي'
        : 'Hello, I would like to ask about a property on Darli';
@endphp

<div id="darli-chat-widget" class="darli-chat-widget" dir="{{ $isAr ? 'rtl' : 'ltr' }}"
    data-wa="{{ $whatsappDigits }}" data-phone="{{ $phoneDigits }}" data-email="{{ $email }}"
    data-default-message="{{ $defaultMessage }}" data-is-ar="{{ $isAr ? '1' : '0' }}">

    {{-- First-visit welcome bubble --}}
    <div class="darli-chat-welcome" id="darli-chat-welcome" role="status">
        <button type="button" class="darli-chat-welcome__close" id="darli-chat-welcome-close"
            aria-label="{{ $isAr ? 'إغلاق' : 'Close' }}">&times;</button>
        <div class="darli-chat-welcome__title">
            <span class="darli-chat-online-dot" aria-hidden="true"></span>
            {{ $isAr ? 'فريق دارلي' : 'Darli team' }}
        </div>
        <p class="darli-chat-welcome__text">{{ $welcomeText }}</p>
        <button type="button" class="darli-chat-welcome__cta" id="darli-chat-welcome-cta">
            {{ $isAr ? 'ابدأ المحادثة' : 'Start chatting' }}
        </button>
    </div>

    {{-- Chat panel --}}
    <div class="darli-chat-panel" id="darli-chat-panel" aria-hidden="true">
        <div class="darli-chat-panel__head">
            <div class="darli-chat-panel__identity">
                <span class="darli-chat-avatar" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                    </svg>
                </span>
                <div>
                    <strong>{{ $isAr ? 'دارلي' : 'Darli' }}</strong>
                    <span class="darli-chat-status"><span class="darli-chat-online-dot"
                            aria-hidden="true"></span>{{ $isAr ? 'متصل الآن' : 'Online now' }}</span>
                </div>
            </div>
            <button type="button" class="darli-chat-close" id="darli-chat-close"
                aria-label="{{ $isAr ? 'إغلاق المحادثة' : 'Close chat' }}">&times;</button>
        </div>

        <div class="darli-chat-body">
            <div class="darli-chat-bubble">
                {{ $chatIntro }}
            </div>

            <form class="darli-chat-form" id="darli-chat-form">
                <input type="text" id="darli-chat-input" class="darli-chat-input"
                    placeholder="{{ $isAr ? 'اكتب رسالتك هنا...' : 'Type your message...' }}"
                    aria-label="{{ $isAr ? 'رسالتك' : 'Your message' }}" autocomplete="off">
                <button type="submit" class="darli-chat-send" aria-label="{{ $isAr ? 'إرسال' : 'Send' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 2 11 13"></path>
                        <path d="M22 2 15 22l-4-9-9-4 20-7z"></path>
                    </svg>
                </button>
            </form>

            <div class="darli-chat-contacts" id="darli-chat-contacts" hidden>
                <div class="darli-chat-contacts__title">
                    {{ $isAr ? 'اختر وسيلة التواصل المناسبة:' : 'Choose how to reach us:' }}
                </div>

                <div class="darli-chat-contact-grid">
                    @if ($whatsappDigits !== '')
                        <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer"
                            class="darli-chat-contact wa" id="darli-chat-wa-link">
                            <span class="darli-chat-contact__icon">
                                <svg width="18" height="18" viewBox="0 0 448 512" fill="currentColor">
                                    <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                                </svg>
                            </span>
                            <span>{{ $isAr ? 'واتساب' : 'WhatsApp' }}</span>
                        </a>
                    @endif

                    @if ($phoneDigits !== '')
                        <a href="tel:{{ $phoneDigits }}" class="darli-chat-contact call">
                            <span class="darli-chat-contact__icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" />
                                </svg>
                            </span>
                            <span>{{ $isAr ? 'اتصال' : 'Call' }}</span>
                        </a>
                    @endif

                    @if ($email !== '')
                        <a href="mailto:{{ $email }}" class="darli-chat-contact email" id="darli-chat-email-link">
                            <span class="darli-chat-contact__icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </span>
                            <span>{{ $isAr ? 'إيميل' : 'Email' }}</span>
                        </a>
                    @endif

                    @if ($facebook !== '')
                        <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer"
                            class="darli-chat-contact social">
                            <span class="darli-chat-contact__icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </span>
                            <span>Facebook</span>
                        </a>
                    @endif

                    @if ($instagram !== '')
                        <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer"
                            class="darli-chat-contact social">
                            <span class="darli-chat-contact__icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                </svg>
                            </span>
                            <span>Instagram</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Floating chat toggle --}}
    <button type="button" class="darli-chat-toggle" id="darli-chat-toggle"
        aria-label="{{ $isAr ? 'تواصل معنا' : 'Chat with us' }}" aria-expanded="false"
        aria-controls="darli-chat-panel">
        <span class="darli-chat-toggle__pulse" aria-hidden="true"></span>
        <svg class="darli-chat-icon-open" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
        </svg>
        <svg class="darli-chat-icon-close" width="22" height="22" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18M6 6l12 12" />
        </svg>
        <span class="darli-chat-toggle__badge" id="darli-chat-badge">1</span>
    </button>
</div>

<style>
    .darli-chat-widget {
        display: none;
        font-family: 'Cairo', 'Almarai', sans-serif;
    }

    @media (max-width: 991px) {
        .darli-chat-widget {
            position: fixed;
            bottom: calc(env(safe-area-inset-bottom, 0px) + 20px);
            right: 20px;
            z-index: 999999;
            display: block;
            direction: inherit;
        }

        .darli-chat-toggle {
            position: relative;
            width: 52px;
            height: 52px;
            border: 0;
            border-radius: 50%;
            background: linear-gradient(135deg, #cf9a3f 0%, #a87627 100%);
            color: #ffffff;
            box-shadow: 0 8px 24px rgba(169, 118, 39, 0.38);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .darli-chat-toggle:active {
            transform: scale(0.94);
        }

        .darli-chat-toggle__pulse {
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid rgba(207, 154, 63, 0.45);
            animation: darliChatPulse 2.4s ease-out infinite;
            pointer-events: none;
        }

        @keyframes darliChatPulse {
            0% { transform: scale(0.9); opacity: 0.9; }
            70% { transform: scale(1.25); opacity: 0; }
            100% { transform: scale(1.25); opacity: 0; }
        }

        .darli-chat-toggle .darli-chat-icon-close,
        .darli-chat-widget.is-open .darli-chat-toggle .darli-chat-icon-open {
            display: none;
        }

        .darli-chat-widget.is-open .darli-chat-toggle .darli-chat-icon-close {
            display: block;
        }

        .darli-chat-toggle__badge {
            position: absolute;
            top: -3px;
            left: -3px;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            border-radius: 10px;
            background: #e32c2b;
            color: #ffffff;
            border: 2px solid #ffffff;
            font-size: 10.5px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .darli-chat-widget.is-open .darli-chat-toggle__badge,
        .darli-chat-widget.is-contacts .darli-chat-toggle__badge {
            display: none;
        }

        /* First-visit welcome bubble */
        .darli-chat-welcome {
            position: absolute;
            bottom: 66px;
            right: 0;
            width: 250px;
            padding: 14px 16px 16px;
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid rgba(201, 147, 59, 0.35);
            box-shadow: 0 14px 38px rgba(15, 23, 42, 0.2);
            display: none;
        }

        .darli-chat-welcome.is-visible {
            display: block;
            animation: darliChatFadeUp 0.3s ease-out;
        }

        .darli-chat-welcome__close {
            position: absolute;
            top: 6px;
            left: 8px;
            width: 24px;
            height: 24px;
            border: 0;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            font-size: 17px;
            line-height: 1;
            cursor: pointer;
        }

        [dir="ltr"] .darli-chat-welcome__close {
            left: auto;
            right: 8px;
        }

        .darli-chat-welcome__title {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #0f2d3c;
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 6px;
        }

        .darli-chat-online-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #16a34a;
            display: inline-block;
            flex-shrink: 0;
        }

        .darli-chat-welcome__text {
            margin: 0 0 12px;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            line-height: 1.7;
        }

        .darli-chat-welcome__cta {
            width: 100%;
            min-height: 38px;
            border: 0;
            border-radius: 10px;
            background: #0f2d3c;
            color: #ffffff;
            font: inherit;
            font-size: 12.5px;
            font-weight: 800;
            cursor: pointer;
        }

        /* Chat panel */
        .darli-chat-panel {
            position: absolute;
            bottom: 66px;
            right: 0;
            width: min(320px, calc(100vw - 36px));
            max-height: min(70vh, 520px);
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.24);
            overflow: hidden;
            display: none;
            flex-direction: column;
        }

        .darli-chat-widget.is-open .darli-chat-panel {
            display: flex;
            animation: darliChatFadeUp 0.25s ease-out;
        }

        @keyframes darliChatFadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .darli-chat-panel__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            background: #0f2d3c;
            color: #ffffff;
        }

        .darli-chat-panel__identity {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .darli-chat-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            color: #e5b158;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .darli-chat-panel__identity strong {
            display: block;
            font-size: 14px;
            font-weight: 900;
            line-height: 1.2;
        }

        .darli-chat-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #cbd5e1;
            font-size: 10.5px;
            font-weight: 600;
            margin-top: 2px;
        }

        .darli-chat-close {
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            flex-shrink: 0;
        }

        .darli-chat-body {
            padding: 14px;
            overflow-y: auto;
        }

        .darli-chat-bubble {
            background: #f1f5f9;
            color: #334155;
            border-radius: 14px 14px 14px 4px;
            padding: 11px 13px;
            font-size: 12.5px;
            font-weight: 600;
            line-height: 1.7;
            margin-bottom: 12px;
        }

        [dir="ltr"] .darli-chat-bubble {
            border-radius: 14px 14px 4px 14px;
        }

        .darli-chat-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .darli-chat-input {
            flex: 1;
            min-width: 0;
            height: 44px;
            padding: 0 12px;
            border: 1px solid #dbe4ee;
            border-radius: 11px;
            background: #f8fafc;
            color: #0f2d3c;
            font: inherit;
            font-size: 12.5px;
            font-weight: 600;
            outline: none;
            box-sizing: border-box;
        }

        .darli-chat-input:focus {
            border-color: #c9933b;
            box-shadow: 0 0 0 3px rgba(201, 147, 59, 0.16);
            background: #ffffff;
        }

        .darli-chat-send {
            width: 44px;
            height: 44px;
            border: 0;
            border-radius: 11px;
            background: linear-gradient(135deg, #d4a03c, #b88628);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .darli-chat-contacts {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #eef2f7;
        }

        .darli-chat-contacts[hidden] {
            display: none !important;
        }

        .darli-chat-contacts__title {
            color: #475569;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 9px;
        }

        .darli-chat-contact-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .darli-chat-contact {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 42px;
            padding: 0 10px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #0f2d3c;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.15s ease, background 0.15s ease;
        }

        .darli-chat-contact:active {
            transform: scale(0.97);
        }

        .darli-chat-contact__icon {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .darli-chat-contact.wa {
            background: #e7f8ee;
            color: #128c4b;
        }

        .darli-chat-contact.wa .darli-chat-contact__icon {
            color: #25d366;
        }

        .darli-chat-contact.call {
            background: #eef4ff;
            color: #1d4ed8;
        }

        .darli-chat-contact.call .darli-chat-contact__icon {
            color: #1d4ed8;
        }

        .darli-chat-contact.email {
            background: #fdf6e8;
            color: #9a6b1d;
        }

        .darli-chat-contact.email .darli-chat-contact__icon {
            color: #c9933b;
        }

        .darli-chat-contact.social {
            background: #f1f5f9;
            color: #334155;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const widget = document.getElementById('darli-chat-widget');
        if (!widget || widget.dataset.darliChatReady === '1') return;
        widget.dataset.darliChatReady = '1';

        const toggle = document.getElementById('darli-chat-toggle');
        const panel = document.getElementById('darli-chat-panel');
        const closeBtn = document.getElementById('darli-chat-close');
        const welcome = document.getElementById('darli-chat-welcome');
        const welcomeClose = document.getElementById('darli-chat-welcome-close');
        const welcomeCta = document.getElementById('darli-chat-welcome-cta');
        const form = document.getElementById('darli-chat-form');
        const input = document.getElementById('darli-chat-input');
        const contacts = document.getElementById('darli-chat-contacts');
        const waLink = document.getElementById('darli-chat-wa-link');
        const emailLink = document.getElementById('darli-chat-email-link');
        const badge = document.getElementById('darli-chat-badge');

        const isArabic = widget.getAttribute('data-is-ar') === '1';
        const waDigits = widget.getAttribute('data-wa') || '';
        const email = widget.getAttribute('data-email') || '';
        const defaultMessage = widget.getAttribute('data-default-message') || '';
        const welcomeKey = 'darli_chat_welcome_seen';

        function hideWelcome() {
            if (welcome) welcome.classList.remove('is-visible');
        }

        function showWelcomeOnce() {
            if (!welcome) return;

            let seen = false;
            try {
                seen = window.sessionStorage.getItem(welcomeKey) === '1';
            } catch (err) {}

            if (seen) return;

            window.setTimeout(function () {
                if (!widget.classList.contains('is-open')) {
                    welcome.classList.add('is-visible');
                }
            }, 1800);

            window.setTimeout(hideWelcome, 12000);
        }

        function markWelcomeSeen() {
            try {
                window.sessionStorage.setItem(welcomeKey, '1');
            } catch (err) {}
        }

        function openChat() {
            widget.classList.add('is-open');
            hideWelcome();
            markWelcomeSeen();
            if (badge) badge.style.display = 'none';
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
            if (panel) panel.setAttribute('aria-hidden', 'false');
            if (input) {
                window.setTimeout(function () {
                    try { input.focus({ preventScroll: true }); } catch (err) {}
                }, 180);
            }
        }

        function closeChat() {
            widget.classList.remove('is-open');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
            if (panel) panel.setAttribute('aria-hidden', 'true');
        }

        function currentMessage() {
            const value = input ? input.value.trim() : '';
            return value !== '' ? value : defaultMessage;
        }

        function updateContactLinks() {
            const message = currentMessage();

            if (waLink && waDigits !== '') {
                waLink.href = 'https://wa.me/' + waDigits + '?text=' + encodeURIComponent(message);
            }

            if (emailLink && email !== '') {
                const subject = isArabic ? 'استفسار عقاري من موقع دارلي' : 'Property inquiry from Darli';
                emailLink.href = 'mailto:' + email + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(message);
            }
        }

        function showContacts() {
            if (!contacts) return;

            updateContactLinks();
            contacts.hidden = false;
            widget.classList.add('is-contacts');
            if (badge) badge.style.display = 'none';
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                if (widget.classList.contains('is-open')) {
                    closeChat();
                } else {
                    openChat();
                }
            });
        }

        if (closeBtn) closeBtn.addEventListener('click', closeChat);

        if (welcomeClose) {
            welcomeClose.addEventListener('click', function (e) {
                e.stopPropagation();
                hideWelcome();
                markWelcomeSeen();
            });
        }

        if (welcomeCta) {
            welcomeCta.addEventListener('click', function (e) {
                e.stopPropagation();
                openChat();
            });
        }

        if (input) {
            input.addEventListener('input', function () {
                if (input.value.trim() !== '') {
                    showContacts();
                } else if (contacts) {
                    contacts.hidden = true;
                    widget.classList.remove('is-contacts');
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                if (!input || input.value.trim() === '') {
                    if (input) input.focus();
                    return;
                }

                showContacts();
            });
        }

        showWelcomeOnce();
    });
</script>
