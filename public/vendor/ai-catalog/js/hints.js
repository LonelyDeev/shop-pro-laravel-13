/* ============================================================
   AICatalog — دستیار شناور هوشمند (runtime سراسری — بدون ویرایش layout)
   • تولتیپ شناور برای عناصر دارای title یا data-ac-hint
   • ★ دکمهٔ «دستیار هوشمند» همیشه و در «همهٔ» صفحات ادمین ساخته
     می‌شود (محصول، مقاله، صفحه، کامنت و هر بخش دیگری) — بدون نیاز
     به هیچ کدی در آن صفحه.
   • ★ v1.5.0 — فیکس تشخیص فیلدهای سئو (عنوان سئو / توضیحات سئو /
     کلمات کلیدی) در فرم‌های واقعی محصول/مقاله/صفحه:
       – فیلدهای داخل «تب‌های مخفی» (مثل تب تنظیمات سئو) هم شناسایی
         می‌شوند (قبل‌تر فقط فیلدهای مرئی تشخیص داده می‌شدند).
       – ویجت تگ‌ها (jQuery tagsInput / bootstrap-tagsinput) پشتیبانی
         کامل: تگ واقعی اضافه می‌شود و چیپ‌های ویجت به‌روز می‌شوند.
       – هنگام درج، اگر فیلد داخل تب دیگری باشد، همان تب «خودکار
         فعال» می‌شود تا کاربر نتیجه را ببیند.
       – ادیتورها (CKEditor/TinyMCE) هنگام درج «تنبل» پیدا می‌شوند
         (اگر بعد از بارگذاری صفحه ساخته شده باشند هم کار می‌کند).
   • ★ تب «سئوچک»: تحلیل زندهٔ سئوی همین فرم (طول عنوان/توضیحات،
     کلمات کلیدی، وجود کلمهٔ کلیدی، حجم محتوا) + امتیاز ۰ تا ۱۰۰ +
     دکمهٔ «رفع با AI» برای هر مورد ناقص — کاملاً محلی و بدون سرور.
   • ★ تب «تاریخچه»: آخرین ۲۵ تولید روی همین مرورگر (localStorage)
     با درج مجدد یک‌کلیکی در فرم.
   • ★ زمینهٔ هوشمند: مقادیر فعلی فرم (عنوان/دسته/برند/سئوهای فعلی)
     به‌عنوان context به AI فرستاده می‌شود تا خروجی دقیق‌تر شود؛
     به‌همراه شمارش کلمه و دکمهٔ «تولید مجدد» در نتیجه.
   • نشانه‌گذاری دستی: data-ac-field="keywords" روی هر input فیلد را
     برای دستیار تعریف می‌کند و data-ac-skip="1" آن را حذف می‌کند.
   • تنظیمات از window.AC_HINTS_CFG (تزریق خودکار توسط ماژول):
     { enabled, position, scope, assist, guideUrl, settingsUrl,
       assistUrl, csrf }
   ============================================================ */
(function () {
    'use strict';

    if (window.__AC_HINTS__) { return; }
    window.__AC_HINTS__ = true;

    var CFG = window.AC_HINTS_CFG || {};
    var POSITIONS = ['bottom-left', 'bottom-right', 'bottom-center', 'top-left', 'top-right'];
    var POSITION = POSITIONS.indexOf(String(CFG.position || '')) !== -1 ? CFG.position : 'bottom-left';
    var ASSIST_URL = String(CFG.assistUrl || '') || null;
    var ASSIST_TPL_URL = String(CFG.assistTemplatesUrl || '') || null;
    var ASSIST_ON = CFG.assist !== false;

    /* ★ v1.6.0 — کتابخانهٔ قالب‌های پرامپت (فقط یک‌بار واکشی می‌شود) */
    var TPLS_RAW = {};
    var TPLS_LOADED = false;

    function kindOfPage() {
        return pageKind || 'auto';
    }

    function loadTemplates() {
        if (TPLS_LOADED || !ASSIST_TPL_URL) { return; }
        TPLS_LOADED = true; // فقط یک‌بار تلاش شود
        fetch(ASSIST_TPL_URL + '?kind=' + encodeURIComponent(kindOfPage()), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d || !d.success || !d.templates) { return; }
                var sel = document.getElementById('ac-tpl');
                if (!sel) { return; }
                d.templates.forEach(function (t) {
                    TPLS_RAW[t.id] = String(t.prompt || '');
                    var opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = (t.content_type === 'generic' ? 'عمومی — ' : '') + t.name;
                    sel.appendChild(opt);
                });
            })
            .catch(function () { /* بدون قالب ادامه می‌دهیم */ });
    }

    function selectedTemplatePrompt() {
        var sel = document.getElementById('ac-tpl');
        if (!sel || !sel.value) { return ''; }
        var prompt = TPLS_RAW[sel.value] || '';
        if (!prompt) { return ''; }
        // متغیرهای قابل جایگزینی سمت کلاینت
        var topicVal = (document.getElementById('ac-topic') || {}).value || pageTopic() || '';
        prompt = prompt.replace(/\{name\}/g, topicVal).replace(/\{title\}/g, topicVal);
        return String(prompt).slice(0, 2500);
    }

    /* ---------- آیکون‌های SVG داخلی (مستقل از نسخهٔ Font Awesome قالب) ---------- */
    var SVG = {
        wand: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4V2m0 20v-2m5-15.5 1.4-1.4M4.6 19.4 6 18m12.4 1.4 1.4 1.4M2.6 4.6 4 6m9.8 2.2L3 19.6a1.5 1.5 0 0 0 2.1 2.1l10.8-10.8M13 3l1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2zm7 8 .7 1.3 1.3.7-1.3.7L20 15l-.7-1.3-1.3-.7 1.3-.7L20 11z"/></svg>',
        book: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/></svg>',
        spark: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l1.9 5.7L19.6 9.6l-5.7 1.9L12 17.2l-1.9-5.7L4.4 9.6l5.7-1.9L12 2zm6.5 10.5l.9 2.6 2.6.9-2.6.9-.9 2.6-.9-2.6-2.6-.9 2.6-.9.9-2.6z"/></svg>',
        check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>',
        gauge: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15l3.5-3.5"/><path d="M20.3 17.5a9.9 9.9 0 1 0-16.6 0"/></svg>',
        clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.2 2.2"/></svg>',
        copy: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>',
        trash: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M6 6l1 15h10l1-15"/></svg>',
        again: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-2.6-6.3M21 3v6h-6"/></svg>',
        warn: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.8 1.9 18a2 2 0 0 0 1.7 3h16.8a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0zM12 9v4M12 17v.01"/></svg>',
        cross: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>'
    };

    function esc(s) {
        return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function faNum(n) {
        return String(n);
    }

    /* ================================================================
     *  تولتیپ‌های شناور
     * ================================================================ */
    var tip = null;

    function ensureTip() {
        if (tip) { return tip; }
        tip = document.createElement('div');
        tip.className = 'ac-hint-tip';
        tip.setAttribute('role', 'tooltip');
        document.body.appendChild(tip);
        return tip;
    }

    function contentOf(el) {
        var custom = el.getAttribute('data-ac-hint');
        if (custom && custom.trim()) { return custom.trim(); }

        var title = el.getAttribute('title');
        if (title && title.trim()) {
            el.setAttribute('data-ac-title', title);
            el.removeAttribute('title');
            return title.trim();
        }

        return el.getAttribute('data-ac-title') || '';
    }

    function showTip(el, text) {
        if (!text) { return; }
        var t = ensureTip();
        t.textContent = text;
        t.classList.add('show');
        positionTip(el, t);
    }

    function positionTip(el, t) {
        t.style.visibility = 'hidden';
        t.style.left = '0px';
        t.style.top = '0px';

        var rect = el.getBoundingClientRect();
        var tw = t.offsetWidth, th = t.offsetHeight;
        var vw = window.innerWidth, vh = window.innerHeight;

        var above = rect.top > th + 14;
        var left = rect.left + (rect.width / 2) - (tw * 0.35);
        left = Math.max(8, Math.min(left, vw - tw - 8));

        var top = above ? rect.top - th - 8 : rect.bottom + 8;
        if (top + th > vh - 8) { top = vh - th - 8; }
        if (top < 8) { top = 8; }

        t.style.left = left + 'px';
        t.style.top = top + 'px';
        t.style.visibility = '';
        t.classList.toggle('below', !above);
    }

    function hideTip() {
        if (tip) { tip.classList.remove('show'); }
    }

    document.addEventListener('mouseover', function (e) {
        var el = e.target.closest ? e.target.closest('[data-ac-hint], [title], [data-ac-title]') : null;
        if (!el) { return; }
        showTip(el, contentOf(el));
    });

    document.addEventListener('mouseout', function (e) {
        var el = e.target.closest ? e.target.closest('[data-ac-hint], [title], [data-ac-title]') : null;
        if (el && !(e.relatedTarget && el.contains(e.relatedTarget))) { hideTip(); }
    });

    document.addEventListener('focusin', function (e) {
        var el = e.target.closest ? e.target.closest('[data-ac-hint], [title], [data-ac-title]') : null;
        if (!el) { return; }
        showTip(el, contentOf(el));
    });

    document.addEventListener('focusout', hideTip);
    window.addEventListener('scroll', hideTip, { passive: true });
    window.addEventListener('resize', hideTip, { passive: true });

    /* ================================================================
     *  CSRF — از کانفیگ تزریقی / متا / فرم / کوکی
     * ================================================================ */
    function csrfToken() {
        if (CFG.csrf) { return String(CFG.csrf); }
        var m = document.querySelector('meta[name="csrf-token"], meta[name="_token"]');
        if (m && m.content) { return m.content; }
        var i = document.querySelector('input[name="_token"]');
        if (i && i.value) { return i.value; }
        var mm = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
        if (mm) {
            try { return decodeURIComponent(mm[1]); } catch (e) { return mm[1]; }
        }
        return '';
    }

    function xsrfHeader() {
        var mm = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
        if (mm) {
            try { return decodeURIComponent(mm[1]); } catch (e) { return mm[1]; }
        }
        return null;
    }

    /* ================================================================
     *  تشخیص نوع صفحه و فیلدهای فرم
     *
     *  v1.5.0 — فیلدهای داخل «تب‌های مخفی» (مثل تب تنظیمات سئو در
     *  فرم‌های واقعی محصول/مقاله/صفحهٔ اپاشاپ) هم شناسایی می‌شوند؛
     *  فقط وقتی دو فیلد هم‌نقش پیدا شود، «مرئی» اولویت می‌گیرد.
     * ================================================================ */
    var SKIP_RE = /قیمت|وزن|موجودی|تعداد|کد\s*کالا|sku|slug|لینک|آدرس|ایمیل|تلفن|موبایل|شناسه|تاریخ|ساعت|پسورد|رمز|price|weight|stock|quantity|qty|sku|code|slug|link|url|email|phone|mobile|password|date|time|_token|csrf|max_?file|upload|file\b/i;

    var PATTERNS = [
        { key: 'seo_title',       re: /عنوان\s*سئو|عنوان\s*متا|seo[\s_.-]*title|meta[\s_.-]*title|title[\s_.-]*tag/i },
        { key: 'seo_description', re: /توضیح\s*(ات)?\s*(سئو|متا)|seo[\s_.-]*desc|meta[\s_.-]*desc/i },
        { key: 'keywords',        re: /کلمات?\s*کلیدی|برچسب|کلیدواژه|keywords|meta_keyword|tags\b/i },
        { key: 'reply',           re: /پاسخ|جواب|reply|response/i, textarea: true },
        { key: 'short_description', re: /خلاصه|مقدمه|کوتاه|short[\s_.-]*desc|summary|excerpt|lead|intro/i },
        { key: 'content',         re: /محتوا|متن\s*کامل|content|body|متن\s*مقاله|editor/i, textarea: true },
        { key: 'description',     re: /توضیح|شرح|description|^desc$/i, textarea: true },
        { key: 'title',           re: /عنوان|تیتر|موضوع|subject|^title$|^name$/i }
    ];

    var FIELD_LABELS = {
        title: 'عنوان',
        short_description: 'توضیح کوتاه',
        description: 'توضیحات',
        content: 'محتوا',
        seo_title: 'عنوان سئو',
        seo_description: 'توضیح سئو',
        keywords: 'کلمات کلیدی',
        reply: 'متن پاسخ'
    };

    var FIELD_KEYS = ['title', 'short_description', 'description', 'content', 'seo_title', 'seo_description', 'keywords', 'reply'];

    function detectKind() {
        var p = decodeURIComponent(location.pathname + ' ' + location.search).toLowerCase();
        if (/comment|کامنت|نظر|دیدگاه/.test(p)) { return 'comment'; }
        if (/post|blog|article|مقاله|وبلاگ|بلاگ|news|خبر/.test(p)) { return 'post'; }
        if (/page|صفحه|about|درباره|contact|تماس|terms|قوانین/.test(p)) { return 'page'; }
        if (/product|محصول|catalog|دسته|category/.test(p)) { return 'product'; }
        return 'auto';
    }

    function labelOf(el) {
        // ۱) label[for]
        var id = el.id;
        if (id) {
            var lb = document.querySelector('label[for="' + id.replace(/"/g, '\\"') + '"]');
            if (lb) { return lb.textContent || ''; }
        }
        // ۲) label داخل والد نزدیک
        var wrap = el.closest('.form-group, .field, .form-row, .mb-3, .col-md-6, .col-md-12, .col-12, .col-sm-12, .form-line, .form-material, fieldset');
        if (wrap) {
            var wl = wrap.querySelector('label, .control-label, .label');
            if (wl && !wl.contains(el)) { return wl.textContent || ''; }
        }
        // ۳) placeholder / aria-label
        return el.placeholder || el.getAttribute('aria-label') || '';
    }

    function isVisible(el) {
        try {
            return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
        } catch (e) { return true; }
    }

    function editorOf(el) {
        // CKEditor 4 — نمونه بر اساس id یا name تکست‌اریا
        try {
            if (window.CKEDITOR && window.CKEDITOR.instances) {
                var inst = window.CKEDITOR.instances[el.id] || window.CKEDITOR.instances[el.name];
                if (inst) { return { type: 'ckeditor', inst: inst, id: el.id || el.name }; }
            }
        } catch (e) { /* CKEditor در دسترس نیست */ }
        // TinyMCE
        try {
            if (window.tinymce) {
                var t = (el.id && window.tinymce.get(el.id)) || null;
                if (t) { return { type: 'tinymce', inst: t, id: el.id }; }
            }
        } catch (e) { /* TinyMCE در دسترس نیست */ }
        return null;
    }

    /* ---------- ویجت تگ‌ها (tagsInput اپاشاپ / bootstrap-tagsinput) ---------- */
    function tagsWidgetOf(el) {
        var jq = window.jQuery || (window.$ && window.$.fn ? window.$ : null);
        if (!jq) { return null; }
        try {
            var $el = jq(el);

            // bootstrap-tagsinput
            if (typeof $el.tagsinput === 'function' && typeof $el.data === 'function' && $el.data('tagsinput')) {
                return { type: 'bs', jq: jq };
            }
            // jQuery tagsInput (xoxco): input اصلی مخفی می‌شود و
            // #id_tagsinput + data-tagsinput-init ساخته می‌شود
            var initAttr = el.getAttribute('data-tagsinput-init');
            var holder = el.id ? document.getElementById(el.id + '_tagsinput') : null;
            if (initAttr || holder) { return { type: 'xoxco', jq: jq }; }
            var par = el.parentNode;
            if (par && par.querySelector('.tagsinput')) { return { type: 'xoxco', jq: jq }; }
        } catch (e) { /* نادیده */ }
        return null;
    }

    function splitKeywords(text) {
        return String(text || '')
            .replace(/[\[\]"]/g, '') // آرایهٔ JSON احتمالی
            .split(/[،,;؛\n]+/)
            .map(function (t) { return t.trim(); })
            .filter(function (t) { return t.length > 1; });
    }

    function detectFields(form) {
        var out = {};
        if (!form) { return out; }

        var els = form.querySelectorAll('input[type=text], input:not([type]), textarea, [contenteditable="true"]');
        Array.prototype.forEach.call(els, function (el) {
            if (el.disabled || el.readOnly) { return; }
            if (el.getAttribute('data-ac-skip')) { return; }
            if (el.closest && el.closest('template')) { return; }
            var tag = (el.tagName || '').toLowerCase();

            var sig = [
                el.name || '', el.id || '', el.placeholder || '',
                el.className || '', labelOf(el) || ''
            ].join(' ');

            if (SKIP_RE.test(sig)) { return; }

            var visible = isVisible(el);
            var w = tagsWidgetOf(el); // ویجت تگ: input مخفی ولی واقعی است

            /* نشانهٔ دستی: data-ac-field */
            var forced = (el.getAttribute('data-ac-field') || '').trim();
            if (forced && FIELD_KEYS.indexOf(forced) !== -1) {
                out[forced] = makeField(el, forced, true);
                out[forced].tags = w;
                return;
            }

            for (var i = 0; i < PATTERNS.length; i++) {
                var p = PATTERNS[i];
                if (p.textarea && tag !== 'textarea' && !el.isContentEditable) { continue; }
                if (p.re.test(sig)) {
                    var prev = out[p.key];
                    // اولویت: فیلد مرئی یا ویجت تگ بر فیلد مخفیِ غیرِویجتی
                    var better = !prev
                        || (visible && !prev.visible)
                        || (w && !prev.tags && !prev.visible);
                    if (better) {
                        out[p.key] = makeField(el, p.key, visible);
                        out[p.key].tags = w;
                    }
                    break;
                }
            }
        });

        return out;

        function makeField(el, key, visible) {
            var isTextarea = (el.tagName || '').toLowerCase() === 'textarea' || el.isContentEditable;
            var lbl = (labelOf(el) || FIELD_LABELS[key] || key).trim().slice(0, 40) || FIELD_LABELS[key];
            return {
                el: el,
                label: lbl,
                editor: isTextarea ? editorOf(el) : null,
                isTextarea: isTextarea,
                visible: visible
            };
        }
    }

    function pickBestForm() {
        var forms = document.querySelectorAll('form');
        if (!forms.length) { return null; }

        var best = null, bestFields = null, bestScore = -1;
        Array.prototype.forEach.call(forms, function (f) {
            var fields = detectFields(f);
            var score = Object.keys(fields).length;
            if (fields.title) { score += 6; }
            if (fields.description || fields.content) { score += 3; }
            if (fields.seo_title || fields.keywords) { score += 2; }
            if (f.contains(document.activeElement)) { score += 2; }
            if (score > bestScore) { best = f; bestFields = fields; bestScore = score; }
        });

        if (bestScore <= 0) { return null; }
        return { form: best, fields: bestFields };
    }

    /* ================================================================
     *  خواندن مقدار فعلی فیلد (برای سئوچک و زمینهٔ هوشمند)
     * ================================================================ */
    function readText(f) {
        try {
            if (!f.editor && f.isTextarea) { f.editor = editorOf(f.el); } // resolve تنبل
            if (f.editor && f.editor.type === 'ckeditor' && f.editor.inst) {
                return String(f.editor.inst.getData() || '');
            }
            if (f.editor && f.editor.type === 'tinymce' && f.editor.inst) {
                return String(f.editor.inst.getContent() || '');
            }
            if (f.el.isContentEditable) { return f.el.innerText || f.el.textContent || ''; }
            return String(f.el.value || '');
        } catch (e) { return ''; }
    }

    function wordCount(text) {
        var t = String(text || '').replace(/<[^>]+>/g, ' ');
        var m = t.match(/[\w\u0600-\u06FF]+/g);
        return m ? m.length : 0;
    }

    /* ================================================================
     *  درج متن در فیلد (+ ادیتورها + ویجت تگ‌ها + فعال‌سازی تب)
     * ================================================================ */
    function fireInput(el) {
        try {
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (e) { /* مرورگر قدیمی */ }
    }

    function activateTab(el) {
        try {
            var pane = el.closest ? el.closest('.tab-pane') : null;
            if (!pane || !pane.id) { return; }
            if (isVisible(pane)) { return; }
            var pid = pane.id.replace(/"/g, '\\"');
            var links = document.querySelectorAll(
                '[data-toggle="tab"][href="#' + pid + '"],' +
                '[data-bs-toggle="tab"][href="#' + pid + '"],' +
                '[aria-controls="' + pid + '"],' +
                '[data-tab="' + pid + '"],' +
                'a[href="#' + pid + '"]'
            );
            for (var i = 0; i < links.length; i++) {
                if (isVisible(links[i])) { links[i].click(); return; }
            }
            pane.classList.add('active'); // پشتیبان بصری
        } catch (e) { /* نادیده */ }
    }

    function widgetHolder(f) {
        try {
            if (f.el.id) {
                var h = document.getElementById(f.el.id + '_tagsinput');
                if (h) { return h; }
            }
            var par = f.el.parentNode;
            if (par) {
                var s = par.querySelector('.tagsinput');
                if (s) { return s; }
            }
        } catch (e) { /* نادیده */ }
        return null;
    }

    function applyTags(f, text) {
        var w = f.tags || (f.tags = tagsWidgetOf(f.el));
        var tags = splitKeywords(text);
        if (!tags.length) { return; }

        var done = false;
        try {
            if (w && w.type === 'xoxco' && w.jq) {
                tags.forEach(function (t) { w.jq(f.el).addTag(t); });
                done = true;
            } else if (w && w.type === 'bs' && w.jq) {
                tags.forEach(function (t) { w.jq(f.el).tagsinput('add', t); });
                done = true;
            }
        } catch (e) { done = false; }

        if (!done) {
            f.el.value = tags.join('، ');
            fireInput(f.el);
        }
    }

    function applyText(f, text) {
        var el = f.el;

        // ① اگر فیلد در تب دیگری است، تب را فعال کن تا کاربر نتیجه را ببیند
        activateTab(el);

        // ② ویجت تگ‌ها؟ (input اصلی مخفی است — تگ واقعی باید ساخته شود)
        var w = f.tags || (f.tags = tagsWidgetOf(el));
        if (w) {
            applyTags(f, text);
            var holder = widgetHolder(f) || el;
            holder.classList.add('ac-fill-flash');
            setTimeout(function () { holder.classList.remove('ac-fill-flash'); }, 2200);
            try { holder.scrollIntoView({ block: 'center', behavior: 'smooth' }); } catch (e) { /* نادیده */ }
            return;
        }

        // ③ ادیتورها (resolve تنبل — CKEditor ممکن است بعد از صفحه‌ساخته شده باشد)
        if (!f.editor && f.isTextarea) { f.editor = editorOf(el); }

        try {
            if (f.editor && f.editor.type === 'ckeditor' && f.editor.inst) {
                f.editor.inst.setData(text);
                if (f.editor.inst.updateElement) { f.editor.inst.updateElement(); }
            } else if (f.editor && f.editor.type === 'tinymce' && f.editor.inst) {
                f.editor.inst.setContent(text);
                if (f.editor.inst.save) { f.editor.inst.save(); }
            } else if (el.isContentEditable) {
                el.innerHTML = text;
            } else {
                el.value = text;
                fireInput(el);
            }
        } catch (e) {
            try { el.value = text; } catch (e2) { /* نادیده */ }
        }

        flash(f);
    }

    function flash(f) {
        var target = f.el;
        if (f.editor && f.editor.type === 'ckeditor') {
            var frame = f.editor.inst.container ? f.editor.inst.container.$ : null;
            var area = frame ? frame.querySelector('.cke_wysiwyg_frame, .cke_contents') : null;
            if (area) { target = area; }
        }
        target.classList.add('ac-fill-flash');
        setTimeout(function () { target.classList.remove('ac-fill-flash'); }, 2200);
        try { target.scrollIntoView({ block: 'center', behavior: 'smooth' }); } catch (e) { /* نادیده */ }
    }

    /* ================================================================
     *  فراخوانی سرور
     * ================================================================ */
    function assistCall(payload, onDone, onErr) {
        if (!ASSIST_URL) {
            onErr('آدرس سرویس تولید در این صفحه در دسترس نیست — یک‌بار صفحه را با Ctrl+F5 رفرش کنید و اگر ادامه داد php artisan optimize:clear را اجرا کنید.');
            return;
        }

        var headers = { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        var tk = csrfToken();
        if (tk) { headers['X-CSRF-TOKEN'] = tk; }
        var xs = xsrfHeader();
        if (xs) { headers['X-XSRF-TOKEN'] = xs; }

        fetch(ASSIST_URL, { method: 'POST', headers: headers, credentials: 'same-origin', body: JSON.stringify(payload) })
            .then(function (r) {
                if (r.status === 403) { throw new Error('دسترسی مجاز نیست — لایسنس ماژول فعال نیست یا نشست شما منقضی شده (یک‌بار خارج و دوباره وارد شوید).'); }
                if (r.status === 404) { throw new Error('سرویس پیدا نشد — روت‌ها کش شده‌اند: در سرور php artisan optimize:clear را اجرا کنید.'); }
                if (r.status === 429) { throw new Error('سهمیهٔ تولید این ساعت تکمیل شد — کمی بعد دوباره تلاش کنید.'); }
                return r.json();
            })
            .then(function (d) {
                if (d && d.success) { onDone(d); }
                else { onErr((d && d.message) || 'پاسخ نامعتبر از سرور.'); }
            })
            .catch(function (e) {
                onErr(e && e.message ? e.message : 'خطا در ارتباط با سرور — اتصال اینترنت و تنظیمات پرووایدر را بررسی کنید.');
            });
    }

    /* ================================================================
     *  تاریخچهٔ تولیدها (localStorage — حداکثر ۲۵ مورد)
     * ================================================================ */
    var HIST_KEY = 'ac_assist_history';
    var histMem = null;

    function loadHist() {
        try {
            var raw = window.localStorage.getItem(HIST_KEY);
            return raw ? (JSON.parse(raw) || []) : [];
        } catch (e) { return histMem || []; }
    }

    function saveHist(list) {
        histMem = list;
        try { window.localStorage.setItem(HIST_KEY, JSON.stringify(list)); } catch (e) { /* حالت خصوصی مرورگر */ }
    }

    function recordHistory(key, label, topic, text) {
        try {
            var list = loadHist();
            list.unshift({
                t: Date.now(),
                k: key,
                l: String(label || FIELD_LABELS[key] || key).slice(0, 40),
                tp: String(topic || '').slice(0, 120),
                x: String(text || '').slice(0, 4000)
            });
            if (list.length > 25) { list.length = 25; }
            saveHist(list);
            renderHist();
        } catch (e) { /* نادیده */ }
    }

    function timeAgo(t) {
        var s = Math.floor((Date.now() - t) / 1000);
        if (s < 45) { return 'همین حالا'; }
        var m = Math.floor(s / 60);
        if (m < 60) { return faNum(m) + ' دقیقه پیش'; }
        var h = Math.floor(m / 60);
        if (h < 24) { return faNum(h) + ' ساعت پیش'; }
        var d = Math.floor(h / 24);
        if (d < 30) { return faNum(d) + ' روز پیش'; }
        try { return new Date(t).toLocaleDateString('fa-IR'); } catch (e) { return ''; }
    }

    function renderHist() {
        var list = document.getElementById('ac-hist-list');
        if (!list) { return; }

        var items = loadHist();
        if (!items.length) {
            list.innerHTML = '<div class="ac-note">هنوز تولیدی ثبت نشده — از تب «تولید» شروع کنید (تاریخچه فقط روی همین مرورگر ذخیره می‌شود).</div>';
            return;
        }

        var html = '';
        items.forEach(function (it, idx) {
            var plain = String(it.x || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
            var prev = plain.length > 110 ? plain.slice(0, 110) + '…' : plain;
            html +=
                '<div class="ac-hist-item" data-idx="' + idx + '">' +
                '<div class="ac-hist-top">' +
                '<span class="ac-hist-badge">' + esc(it.l || it.k) + '</span>' +
                '<span class="ac-hist-time">' + esc(timeAgo(it.t)) + '</span>' +
                '<button type="button" class="ac-hist-del" data-hdel="' + idx + '" aria-label="حذف">&times;</button>' +
                '</div>' +
                (it.tp ? '<div class="ac-hist-topic">موضوع: ' + esc(it.tp) + '</div>' : '') +
                '<div class="ac-hist-prev">' + esc(prev) + '</div>' +
                '<div class="ac-hist-actions">' +
                '<button type="button" class="ac-hist-btn" data-hre="' + idx + '">' + SVG.wand + ' درج در فرم</button>' +
                '<button type="button" class="ac-hist-btn ghost" data-hcp="' + idx + '">' + SVG.copy + ' کپی</button>' +
                '</div>' +
                '</div>';
        });
        list.innerHTML = html;
    }

    function bindHist() {
        var listEl = document.getElementById('ac-hist-list');
        if (!listEl) { return; }

        listEl.addEventListener('click', function (e) {
            var btn = e.target.closest ? e.target.closest('button[data-hdel], button[data-hre], button[data-hcp]') : null;
            if (!btn) { return; }
            var items = loadHist();
            var idx = parseInt(btn.getAttribute('data-hdel') || btn.getAttribute('data-hre') || btn.getAttribute('data-hcp'), 10);
            var it = items[idx];
            if (!it) { return; }

            if (btn.hasAttribute('data-hdel')) {
                items.splice(idx, 1);
                saveHist(items);
                renderHist();
                return;
            }

            if (btn.hasAttribute('data-hcp')) {
                copyText(it.x || '');
                return;
            }

            // درج مجدد
            var f = detected && detected.fields[it.k];
            if (f) {
                applyText(f, it.x || '');
                showToast('درج شد در «' + (it.l || it.k) + '» ✓', 'ok');
            } else {
                copyText(it.x || '');
                showToast('این فیلد در صفحهٔ فعلی نیست — متن کپی شد.', 'warn');
            }
        });

        var clearBtn = document.getElementById('ac-hist-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                saveHist([]);
                renderHist();
                showToast('تاریخچه پاک شد.', 'ok');
            });
        }
    }

    function copyText(text) {
        try {
            navigator.clipboard.writeText(text).then(
                function () { showToast('کپی شد ✓', 'ok'); },
                function () { showToast('کپی خودکار ممکن نشد — متن را دستی انتخاب و کپی کنید.', 'warn'); }
            );
        } catch (e) {
            showToast('کپی خودکار ممکن نشد — متن را دستی انتخاب و کپی کنید.', 'warn');
        }
    }

    /* ================================================================
     *  سئوچک — تحلیل زندهٔ سئوی فرم همین صفحه (کاملاً محلی)
     * ================================================================ */
    function mkCheck(label, state, msg, fixKey) {
        return { label: label, state: state, msg: msg, fixKey: fixKey || null };
    }

    function buildSeoChecks() {
        var F = detected ? detected.fields : {};
        var checks = [];

        function val(k) { return F[k] ? readText(F[k]).trim() : ''; }
        function plain(v) { return String(v || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim(); }
        function has(k) { return !!F[k]; }

        /* ۱) عنوان اصلی */
        if (has('title')) {
            var t = plain(val('title'));
            if (!t) { checks.push(mkCheck('عنوان اصلی', 'bad', 'خالی است', 'title')); }
            else if (t.length < 10) { checks.push(mkCheck('عنوان اصلی', 'warn', faNum(t.length) + ' کاراکتر — کوتاه است', 'title')); }
            else if (t.length > 75) { checks.push(mkCheck('عنوان اصلی', 'warn', faNum(t.length) + ' کاراکتر — بلند است', 'title')); }
            else { checks.push(mkCheck('عنوان اصلی', 'ok', faNum(t.length) + ' کاراکتر ✓')); }
        }

        /* ۲) عنوان سئو */
        if (has('seo_title')) {
            var st = plain(val('seo_title'));
            if (!st) { checks.push(mkCheck('عنوان سئو', 'bad', 'خالی است — مهم‌ترین فیلد SERP', 'seo_title')); }
            else if (st.length < 20) { checks.push(mkCheck('عنوان سئو', 'warn', faNum(st.length) + '/۶۰ — کوتاه است', 'seo_title')); }
            else if (st.length > 65) { checks.push(mkCheck('عنوان سئو', 'warn', faNum(st.length) + '/۶۰ — بلند است و در گوگل بریده می‌شود', 'seo_title')); }
            else { checks.push(mkCheck('عنوان سئو', 'ok', faNum(st.length) + '/۶۰ کاراکتر ✓')); }
        } else if (Object.keys(F).length) {
            checks.push(mkCheck('عنوان سئو', 'bad', 'فیلدش در این فرم پیدا نشد'));
        }

        /* ۳) توضیحات سئو */
        if (has('seo_description')) {
            var sd = plain(val('seo_description'));
            if (!sd) { checks.push(mkCheck('توضیحات سئو', 'bad', 'خالی است', 'seo_description')); }
            else if (sd.length < 100) { checks.push(mkCheck('توضیحات سئو', 'warn', faNum(sd.length) + '/۱۶۰ — کوتاه است', 'seo_description')); }
            else if (sd.length > 175) { checks.push(mkCheck('توضیحات سئو', 'warn', faNum(sd.length) + '/۱۶۰ — بلند است و بریده می‌شود', 'seo_description')); }
            else { checks.push(mkCheck('توضیحات سئو', 'ok', faNum(sd.length) + '/۱۶۰ کاراکتر ✓')); }
        } else if (Object.keys(F).length) {
            checks.push(mkCheck('توضیحات سئو', 'bad', 'فیلدش در این فرم پیدا نشد'));
        }

        /* ۴) کلمات کلیدی */
        var kws = has('keywords') ? splitKeywords(val('keywords')) : [];
        if (has('keywords')) {
            if (!kws.length) { checks.push(mkCheck('کلمات کلیدی (تگ‌ها)', 'bad', 'خالی است', 'keywords')); }
            else if (kws.length < 4) { checks.push(mkCheck('کلمات کلیدی (تگ‌ها)', 'warn', faNum(kws.length) + ' تگ — حداقل ۴ تگ بهتر است', 'keywords')); }
            else { checks.push(mkCheck('کلمات کلیدی (تگ‌ها)', 'ok', faNum(kws.length) + ' تگ ✓')); }
        } else if (Object.keys(F).length) {
            checks.push(mkCheck('کلمات کلیدی (تگ‌ها)', 'bad', 'فیلدش در این فرم پیدا نشد'));
        }

        /* ۵) کلمهٔ کلیدی اول در عنوان سئو */
        if (kws.length && has('seo_title')) {
            var k1 = kws[0];
            var stv = plain(val('seo_title'));
            if (stv && stv.indexOf(k1) !== -1) { checks.push(mkCheck('کلمهٔ کلیدی در عنوان سئو', 'ok', '«' + k1.slice(0, 25) + '» در عنوان هست ✓')); }
            else { checks.push(mkCheck('کلمهٔ کلیدی در عنوان سئو', 'warn', '«' + k1.slice(0, 25) + '» در عنوان سئو نیست', 'seo_title')); }
        }

        /* ۶) کلمهٔ کلیدی اول در توضیحات سئو */
        if (kws.length && has('seo_description')) {
            var k2 = kws[0];
            var sdv = plain(val('seo_description'));
            if (sdv && sdv.indexOf(k2) !== -1) { checks.push(mkCheck('کلمهٔ کلیدی در توضیحات سئو', 'ok', '«' + k2.slice(0, 25) + '» در توضیحات هست ✓')); }
            else { checks.push(mkCheck('کلمهٔ کلیدی در توضیحات سئو', 'warn', '«' + k2.slice(0, 25) + '» در توضیحات سئو نیست', 'seo_description')); }
        }

        /* ۷) توضیح کوتاه / خلاصه */
        if (has('short_description')) {
            var sh = plain(val('short_description'));
            if (!sh) { checks.push(mkCheck('توضیح کوتاه / خلاصه', 'bad', 'خالی است', 'short_description')); }
            else if (sh.length < 40) { checks.push(mkCheck('توضیح کوتاه / خلاصه', 'warn', faNum(sh.length) + ' کاراکتر — کوتاه است', 'short_description')); }
            else { checks.push(mkCheck('توضیح کوتاه / خلاصه', 'ok', faNum(sh.length) + ' کاراکتر ✓')); }
        }

        /* ۸) حجم محتوا */
        var bodyField = has('content') ? 'content' : (has('description') ? 'description' : null);
        if (bodyField) {
            var wc = wordCount(val(bodyField));
            var min = (pageKind === 'post' || pageKind === 'page') ? 250 : 80;
            if (wc === 0) { checks.push(mkCheck('حجم محتوا', 'bad', 'خالی است', bodyField)); }
            else if (wc < min) { checks.push(mkCheck('حجم محتوا', 'warn', faNum(wc) + ' کلمه — برای سئو حداقل ' + faNum(min) + ' کلمه', bodyField)); }
            else { checks.push(mkCheck('حجم محتوا', 'ok', faNum(wc) + ' کلمه ✓')); }
        }

        return checks;
    }

    function ringSvg(score) {
        var C = 2 * Math.PI * 19;
        var off = C * (1 - score / 100);
        var cls = score >= 75 ? 'good' : (score >= 45 ? 'mid' : 'bad');
        return '<svg viewBox="0 0 44 44" class="ac-ring ' + cls + '" role="img" aria-label="امتیاز ' + score + ' از ۱۰۰">' +
            '<circle class="bg" cx="22" cy="22" r="19"></circle>' +
            '<circle class="fg" cx="22" cy="22" r="19" stroke-dasharray="' + C.toFixed(1) + '" stroke-dashoffset="' + off.toFixed(1) + '"></circle>' +
            '<text x="22" y="27" text-anchor="middle">' + faNum(score) + '</text>' +
            '</svg>';
    }

    var SEO_STATE_ICON = { ok: SVG.check, warn: SVG.warn, bad: SVG.cross };
    var SEO_STATE_TXT = { ok: 'ok', warn: 'warn', bad: 'bad' };

    function renderSeo() {
        var mount = document.getElementById('ac-seo-mount');
        if (!mount) { return; }

        if (!detected || !Object.keys(detected.fields).length) {
            mount.innerHTML = '<div class="ac-note">فرمی برای تحلیل پیدا نشد — صفحهٔ ویرایش محصول، مقاله یا صفحه را باز کنید.</div>';
            return;
        }

        var checks = buildSeoChecks();
        if (!checks.length) {
            mount.innerHTML = '<div class="ac-note">فیلد قابل تحلیلی در این فرم نیست.</div>';
            return;
        }

        var score = 0, max = 0, okCount = 0;
        checks.forEach(function (c) {
            max += 1;
            if (c.state === 'ok') { score += 1; okCount += 1; }
            else if (c.state === 'warn') { score += 0.55; }
        });
        score = Math.round((score / max) * 100);

        var rows = '';
        checks.forEach(function (c, i) {
            var canFix = c.fixKey && ASSIST_ON && detected && detected.fields[c.fixKey];
            rows +=
                '<div class="ac-check ' + (SEO_STATE_TXT[c.state] || 'warn') + '">' +
                '<span class="ac-check-ico">' + (SEO_STATE_ICON[c.state] || SVG.warn) + '</span>' +
                '<div class="ac-check-txt"><b>' + esc(c.label) + '</b><small>' + esc(c.msg) + '</small></div>' +
                (canFix ? '<button type="button" class="ac-check-fix" data-seo-fix="' + esc(c.fixKey) + '" title="تولید با AI">' + SVG.wand + '</button>' : '') +
                '</div>';
        });

        mount.innerHTML =
            '<div class="ac-seo-head">' +
            ringSvg(score) +
            '<div class="ac-seo-sum">' +
            '<b>امتیاز سئوی این صفحه</b>' +
            '<small>' + faNum(okCount) + ' از ' + faNum(checks.length) + ' بررسی موفق</small>' +
            '<button type="button" class="ac-seo-again" id="ac-seo-again">' + SVG.again + ' بازبینی مجدد</button>' +
            '</div>' +
            '</div>' +
            '<div class="ac-checks">' + rows + '</div>' +
            (ASSIST_ON ? '' : '<div class="ac-note warn">تولید AI در تنظیمات ماژول غیرفعال است — فقط تحلیل نمایش داده می‌شود.</div>');

        var again = document.getElementById('ac-seo-again');
        if (again) { again.addEventListener('click', function () { renderSeo(); showToast('بازبینی انجام شد ✓', 'ok'); }); }

        var fixBtns = mount.querySelectorAll('[data-seo-fix]');
        Array.prototype.forEach.call(fixBtns, function (b) {
            b.addEventListener('click', function () {
                generateField(b.getAttribute('data-seo-fix'));
            });
        });
    }

    /* ================================================================
     *  پنل دستیار هوشمند
     * ================================================================ */
    var fab = null, panel = null;
    var detected = null;   // { form, fields }
    var pageKind = detectKind();
    var lastGen = null;    // { key, topic } برای «تولید مجدد»

    function pageTopic() {
        // عنوان موضوع: فیلد عنوانِ شناسایی‌شده یا h1 صفحه
        if (detected && detected.fields.title && detected.fields.title.el.value) {
            return detected.fields.title.el.value.trim();
        }
        var h1 = document.querySelector('.content-wrapper h1, main h1, h1');
        if (h1) { return (h1.textContent || '').trim().slice(0, 150); }
        return '';
    }

    /* ---------- زمینهٔ هوشمند: مقادیر فعلی فرم ---------- */
    function collectContext() {
        if (!detected || !detected.form) { return ''; }
        var parts = [];
        try {
            var order = ['title', 'seo_title', 'seo_description', 'keywords', 'short_description'];
            order.forEach(function (k) {
                var f = detected.fields[k];
                if (!f) { return; }
                var v = String(readText(f) || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                if (v) { parts.push(FIELD_LABELS[k] + ': ' + v.slice(0, 140)); }
            });
            // دسته و برند
            var sel = detected.form.querySelector('select[name="category"], select[name="category_id"], select#category');
            if (sel && sel.selectedIndex >= 0 && sel.options[sel.selectedIndex]) {
                var t = sel.options[sel.selectedIndex].text;
                if (t && String(t).trim()) { parts.push('دسته: ' + String(t).trim()); }
            }
            var br = detected.form.querySelector('select[name="brand_id"], select[name="brand"], select#brand');
            if (br && br.selectedIndex >= 0 && br.options[br.selectedIndex]) {
                var bt = br.options[br.selectedIndex].text;
                if (bt && String(bt).trim()) { parts.push('برند: ' + String(bt).trim()); }
            }
        } catch (e) { /* نادیده */ }
        var s = parts.join(' | ');
        return s.length > 700 ? s.slice(0, 700) : s;
    }

    function initAssistant() {
        if (CFG.enabled === false) { return; }

        detected = pickBestForm();

        /* ---------- ساخت دکمهٔ شناور ---------- */
        fab = document.createElement('button');
        fab.type = 'button';
        fab.className = 'ac-help-fab pos-' + POSITION;
        fab.innerHTML = SVG.wand;
        fab.setAttribute('aria-label', 'دستیار هوشمند تولید محتوا');
        document.body.appendChild(fab);

        /* ---------- ساخت پنل چهارتبی ---------- */
        panel = document.createElement('div');
        panel.className = 'ac-help-panel pos-' + POSITION;
        panel.setAttribute('role', 'complementary');
        panel.innerHTML =
            '<div class="ac-help-head">' +
            '<span class="ac-help-logo">' + SVG.spark + '</span>' +
            '<b>دستیار هوشمند</b>' +
            '<button type="button" class="ac-help-close" aria-label="بستن">&times;</button>' +
            '</div>' +
            '<div class="ac-tabs">' +
            '<button type="button" class="ac-tab active" data-tab="gen"><i>' + SVG.wand + '</i>تولید</button>' +
            '<button type="button" class="ac-tab" data-tab="seo"><i>' + SVG.gauge + '</i>سئوچک</button>' +
            '<button type="button" class="ac-tab" data-tab="hist"><i>' + SVG.clock + '</i>تاریخچه</button>' +
            '<button type="button" class="ac-tab" data-tab="help"><i>' + SVG.book + '</i>راهنما</button>' +
            '</div>' +
            '<div class="ac-help-body">' +
            '<div class="ac-tabpane active" id="ac-pane-gen">' + genPaneHtml() + '</div>' +
            '<div class="ac-tabpane" id="ac-pane-seo">' + seoPaneHtml() + '</div>' +
            '<div class="ac-tabpane" id="ac-pane-hist">' + histPaneHtml() + '</div>' +
            '<div class="ac-tabpane" id="ac-pane-help">' + helpPaneHtml() + '</div>' +
            '</div>';
        document.body.appendChild(panel);

        bindPanel();
    }

    /* ---------- HTML تب تولید ---------- */
    function genPaneHtml() {
        if (!ASSIST_ON) {
            return '<div class="ac-note">تب تولید در تنظیمات ماژول غیرفعال شده است.</div>';
        }

        var fields = detected ? detected.fields : null;
        var hasFields = fields && Object.keys(fields).length > 0;

        var html = '';

        // حالت کامنت: جعبهٔ متن کامنت + پاسخ هوشمند
        if (pageKind === 'comment') {
            html +=
                '<div class="ac-field-row">' +
                '<label class="ac-lbl">متن کامنت (برای نوشتن پاسخ):</label>' +
                '<textarea id="ac-comment-text" rows="3" placeholder="متن کامنت کاربر را بنویسید یا بچسبانید…"></textarea>' +
                '</div>' +
                '<div class="ac-field-row">' +
                '<label class="ac-lbl">قالب پاسخ (از کتابخانهٔ قالب‌ها):</label>' +
                '<select id="ac-tpl"><option value="">— بدون قالب —</option></select>' +
                '</div>' +
                '<button type="button" class="ac-gen-btn" id="ac-reply-btn">' + SVG.wand + ' پاسخ هوشمند به کامنت</button>' +
                '<div class="ac-result" id="ac-result" hidden></div>';
            return html;
        }

        if (!ASSIST_URL) {
            html += '<div class="ac-note warn">سرویس تولید در دسترس نیست — php artisan optimize:clear را اجرا و صفحه را رفرش کنید.</div>';
        }

        // موضوع
        html +=
            '<div class="ac-field-row">' +
            '<label class="ac-lbl">موضوع / عنوان:</label>' +
            '<input type="text" id="ac-topic" value="' + esc(pageTopic()) + '" placeholder="مثلاً: گوشی سامسومگ Galaxy A55">' +
            '</div>';

        // ★ v1.6.0 — قالب پرامپت از کتابخانهٔ ماژول
        html +=
            '<div class="ac-field-row">' +
            '<label class="ac-lbl">قالب پرامپت (کتابخانهٔ قالب‌های ماژول):</label>' +
            '<select id="ac-tpl"><option value="">— بدون قالب (دستور عمومی) —</option></select>' +
            '</div>';

        // نوع محتوا + لحن + طول
        html += '<div class="ac-trio">';
        html += '<div><label class="ac-lbl">نوع</label><select id="ac-kind">' +
            '<option value="auto"' + (pageKind === 'auto' ? ' selected' : '') + '>خودکار</option>' +
            '<option value="product"' + (pageKind === 'product' ? ' selected' : '') + '>محصول</option>' +
            '<option value="post"' + (pageKind === 'post' ? ' selected' : '') + '>مقاله</option>' +
            '<option value="page"' + (pageKind === 'page' ? ' selected' : '') + '>صفحه</option>' +
            '<option value="generic">عمومی</option>' +
            '</select></div>';
        html += '<div><label class="ac-lbl">لحن</label><select id="ac-tone">' +
            '<option value="professional">حرفه‌ای</option>' +
            '<option value="friendly">صمیمی</option>' +
            '<option value="technical">فنی</option>' +
            '<option value="luxury">لوکس</option>' +
            '<option value="persuasive">فروش‌محور</option>' +
            '<option value="educational">آموزشی</option>' +
            '<option value="humorous">طنزآمیز</option>' +
            '</select></div>';
        html += '<div><label class="ac-lbl">طول</label><select id="ac-length">' +
            '<option value="short">کوتاه</option>' +
            '<option value="medium" selected>متوسط</option>' +
            '<option value="long">بلند</option>' +
            '</select></div>';
        html += '</div>';

        // فیلدهای شناسایی‌شده
        if (hasFields) {
            html += '<div class="ac-lbl" style="margin-top:10px;">فیلدهای شناسایی‌شده در این صفحه:</div>';
            html += '<div class="ac-fields-list">';
            Object.keys(fields).forEach(function (key) {
                var f = fields[key];
                var em = '';
                if (f.tags) { em = ' <em>(تگ‌ها)</em>'; }
                else if (!isVisible(f.el)) { em = ' <em>(در تب دیگر)</em>'; }
                else if (f.editor) { em = ' <em>(ادیتور)</em>'; }
                html +=
                    '<div class="ac-field-chip" data-field="' + key + '">' +
                    '<span class="ac-chip-lbl">' + esc(f.label || FIELD_LABELS[key] || key) + em + '</span>' +
                    '<button type="button" class="ac-chip-btn" data-gen="' + key + '">' + SVG.wand + '</button>' +
                    '</div>';
            });
            html += '</div>';

            // ★ v1.6.0 — سوییچ بازنویسی متن فعلی
            html +=
                '<label class="ac-rewrite-row" title="به‌جای تولید از صفر، متن فعلی همان فیلد را می‌گیرد و نگارش/سئویش را بهبود می‌دهد">' +
                '<input type="checkbox" id="ac-rewrite">' +
                '<span>' + SVG.again + ' بازنویسی و بهبود متن فعلی (به‌جای تولید از صفر)</span>' +
                '</label>';

            html += '<button type="button" class="ac-gen-btn wide" id="ac-gen-all">' + SVG.wand + ' تولید همهٔ فیلدها</button>';
        } else {
            html +=
                '<div class="ac-note">در این صفحه فیلد قابل تشخیصی پیدا نشد — صفحهٔ ویرایش (فرم) را باز کنید؛ ' +
                'یا از حالت «متن آزاد» زیر استفاده کنید.</div>' +
                '<div class="ac-field-row">' +
                '<label class="ac-lbl">چه متنی می‌خواهید؟ (متن آزاد):</label>' +
                '<textarea id="ac-custom-ask" rows="2" placeholder="مثلاً: یک شعار تبلیغاتی برای صفحهٔ اصلی"></textarea>' +
                '</div>' +
                '<button type="button" class="ac-gen-btn wide" id="ac-custom-btn">' + SVG.wand + ' تولید متن</button>';
        }

        // ناحیهٔ نتیجه
        html += '<div class="ac-result" id="ac-result" hidden></div>';

        return html;
    }

    /* ---------- HTML تب سئوچک ---------- */
    function seoPaneHtml() {
        return '<div id="ac-seo-mount"><div class="ac-note">برای شروع تحلیل، تب را باز کنید…</div></div>';
    }

    /* ---------- HTML تب تاریخچه ---------- */
    function histPaneHtml() {
        return '<div class="ac-hist-head">' +
            '<span>آخرین تولیدها <small>(این مرورگر)</small></span>' +
            '<button type="button" class="ac-hist-clear" id="ac-hist-clear">' + SVG.trash + ' پاک‌کردن</button>' +
            '</div>' +
            '<div class="ac-hist-list" id="ac-hist-list"></div>';
    }

    /* ---------- HTML تب راهنما ---------- */
    function helpPaneHtml() {
        var src = document.querySelector('[data-ac-help]');
        var title, bodyHtml;

        if (src) {
            title = (src.getAttribute('data-ac-help') || 'راهنمای این صفحه').trim() || 'راهنمای این صفحه';
            var tmp = document.createElement('div');
            while (src.firstChild) { tmp.appendChild(src.firstChild); }
            bodyHtml = tmp.innerHTML;
            if (src.parentNode) { src.parentNode.removeChild(src); }
        } else {
            title = 'راهنمای دستیار';
            bodyHtml =
                '<h6>تولید محتوا روی هر فرم</h6>' +
                '<p>دکمهٔ شناور در «همهٔ» صفحات مدیریت ظاهر می‌شود. در صفحات ویرایش محصول، مقاله، صفحه و کامنت، فیلدهای فرم — حتی فیلدهای سئو در تب‌های دیگر — خودکار تشخیص داده می‌شوند و متن هوشمند در همان فیلد درج می‌گردد؛ تب مربوطه هم خودکار فعال می‌شود. سپس با دکمهٔ ذخیرهٔ خود فروشگاه ثبت شود.</p>' +
                '<h6>قالب‌های پرامپت (جدید)</h6>' +
                '<p>در تب «تولید» می‌توانید از «کتابخانهٔ قالب‌های ماژول» (۵۶ قالب آمادهٔ دسته‌بندی‌شده: محصول/مقاله/صفحه/کامنت/عمومی) استفاده کنید؛ قالب انتخاب‌شده به‌عنوان دستور ویژه به هوش مصنوعی فرستاده می‌شود و لحن/طول آن پیشنهادی است.</p>' +
                '<h6>بازنویسی هوشمند (جدید)</h6>' +
                '<p>با فعال‌کردن «بازنویسی و بهبود متن فعلی»، به‌جای تولید از صفر، متن موجود همان فیلد گرفته می‌شود و نگارش/روانی/سئوی آن بهتر می‌شود — معنا و داده‌ها دست‌نخورده می‌مانند. برای متنی که هست و راضی نیستید، همین را روشن کنید.</p>' +
                '<h6>سئوچک</h6>' +
                '<p>در تب «سئوچک»، وضعیت سئوی همین فرم به‌صورت زنده بررسی می‌شود (طول عنوان و توضیحات، کلمات کلیدی و…) و هر مورد ناقص را با دکمهٔ چوب‌جادو می‌توانید همان‌جا با AI بسازید.</p>' +
                '<h6>تاریخچه</h6>' +
                '<p>آخرین تولیدهای شما در همین مرورگر ذخیره می‌شود و با یک کلیک دوباره در فرم درج می‌شود.</p>' +
                '<h6>نشانه‌گذاری دستی فیلد (اختیاری)</h6>' +
                '<p>اگر فیلدی خودکار تشخیص داده نشد، به input آن <code>data-ac-field="keywords"</code> اضافه کنید (مقادیر مجاز: ' + FIELD_KEYS.join('، ') + ')؛ و برای حذف یک فیلد از تشخیص، <code>data-ac-skip="1"</code> بگذارید.</p>' +
                '<h6>موتور کاتالوگ محصولات</h6>' +
                '<p>برای تولید گروهی + گردش کار تأیید روی «محصولات/مقالات/صفحات»، از بخش «موتور محتوای هوشمند» در منو استفاده کنید: کاتالوگ ← انتخاب ← تولید ← بررسی ← تأیید.</p>' +
                (CFG.guideUrl
                    ? '<a href="' + esc(CFG.guideUrl) + '" class="ac-help-link">' + SVG.book + ' راهنمای کامل استفاده</a>'
                    : '') +
                (CFG.settingsUrl
                    ? '<a href="' + esc(CFG.settingsUrl) + '" class="ac-help-link">' + SVG.wand + ' تنظیمات دستیار شناور (موقعیت و نمایش)</a>'
                    : '');
        }

        return bodyHtml;
    }

    /* ---------- رفتار پنل ---------- */
    function bindPanel() {
        function close() {
            panel.classList.remove('open');
            fab.classList.remove('active');
        }
        function toggle() {
            var willOpen = !panel.classList.contains('open');
            panel.classList.toggle('open', willOpen);
            fab.classList.toggle('active', willOpen);
            if (willOpen) {
                // بازخوانی موضوع اگر کاربر عنوان را در فرم پر کرده
                var t = document.getElementById('ac-topic');
                if (t && !t.value.trim()) { t.value = pageTopic(); }
                loadTemplates(); // ★ v1.6.0 — واکشی قالب‌های کتابخانه (یک‌بار)
                renderSeo();
                renderHist();
            }
        }

        fab.addEventListener('click', function (e) { e.stopPropagation(); toggle(); });
        panel.querySelector('.ac-help-close').addEventListener('click', close);
        document.addEventListener('click', function (e) {
            if (panel.classList.contains('open') && !panel.contains(e.target) && !fab.contains(e.target)) { close(); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { close(); }
        });

        // تب‌ها (چهار تب + بارگذاری تنبل محتوای هر تب)
        panel.querySelectorAll('.ac-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                panel.querySelectorAll('.ac-tab').forEach(function (t2) { t2.classList.remove('active'); });
                tab.classList.add('active');
                var target = tab.getAttribute('data-tab');
                ['gen', 'seo', 'hist', 'help'].forEach(function (id) {
                    var pane = document.getElementById('ac-pane-' + id);
                    if (pane) { pane.classList.toggle('active', id === target); }
                });
                if (target === 'seo') { renderSeo(); }
                if (target === 'hist') { renderHist(); }
            });
        });

        bindHist();

        if (!ASSIST_ON) { return; }

        // تولید یک فیلد
        panel.querySelectorAll('[data-gen]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key = btn.getAttribute('data-gen');
                generateField(key);
            });
        });

        // تولید همه
        var allBtn = document.getElementById('ac-gen-all');
        if (allBtn) {
            allBtn.addEventListener('click', function () { generateAll(); });
        }

        // متن آزاد
        var cBtn = document.getElementById('ac-custom-btn');
        if (cBtn) {
            cBtn.addEventListener('click', function () {
                var ask = (document.getElementById('ac-custom-ask').value || '').trim();
                if (!ask) { showToast('ابتدا متن درخواست را بنویسید.', 'warn'); return; }
                callAssistant('custom', ask, null, function (text) {
                    recordHistory('custom', 'متن آزاد', ask, text);
                    showResult(text, null, 'متن تولیدشده (کپی کنید):', { key: 'custom', topic: ask });
                    renderSeo();
                }, btnBusy(cBtn));
            });
        }

        // پاسخ کامنت
        var rBtn = document.getElementById('ac-reply-btn');
        if (rBtn) {
            rBtn.addEventListener('click', function () {
                var txt = (document.getElementById('ac-comment-text').value || '').trim();
                if (!txt) { showToast('متن کامنت را بنویسید یا بچسبانید.', 'warn'); return; }
                var replyField = detected && detected.fields.reply ? detected.fields.reply : null;
                callAssistant('reply', null, txt, function (text) {
                    recordHistory('reply', 'پاسخ کامنت', txt.slice(0, 120), text);
                    if (replyField) {
                        applyText(replyField, text);
                        showResult(text, replyField, 'درج شد در فیلد پاسخ ✓', { key: 'reply', topic: txt.slice(0, 120) });
                    } else {
                        showResult(text, null, 'پاسخ تولیدشده (کپی کنید):', { key: 'reply', topic: txt.slice(0, 120) });
                    }
                }, btnBusy(rBtn));
            });
        }
    }

    function btnBusy(btn) {
        return {
            on: function () { btn.disabled = true; btn.classList.add('busy'); },
            off: function () { btn.disabled = false; btn.classList.remove('busy'); }
        };
    }

    function payloadFor(fieldKey, topic, context, extra) {
        var kindSel = document.getElementById('ac-kind');
        var toneSel = document.getElementById('ac-tone');
        var lenSel = document.getElementById('ac-length');
        var p = {
            kind: kindSel ? kindSel.value : (pageKind === 'comment' ? 'comment' : 'auto'),
            field: fieldKey,
            topic: topic || '',
            context: context !== null && context !== undefined ? context : collectContext(),
            tone: toneSel ? toneSel.value : 'professional',
            length: lenSel ? lenSel.value : 'medium'
        };

        // ★ v1.6.0 — قالب انتخاب‌شده از کتابخانه
        var tplPrompt = selectedTemplatePrompt();
        if (tplPrompt) { p.template_prompt = tplPrompt; }

        // ★ v1.6.0 — حالت بازنویسی
        var rw = document.getElementById('ac-rewrite');
        if (rw && rw.checked) { p.mode = 'rewrite'; }

        if (extra) {
            Object.keys(extra).forEach(function (k) { p[k] = extra[k]; });
        }
        return p;
    }

    function callAssistant(fieldKey, topic, context, onText, busy, extra) {
        if (busy) { busy.on(); }
        var payload = payloadFor(fieldKey, topic, context, extra || null);
        assistCall(payload, function (d) {
            if (busy) { busy.off(); }
            onText(String(d.text || ''));
        }, function (msg) {
            if (busy) { busy.off(); }
            showToast(msg, 'err');
        });
    }

    function generateField(key) {
        var f = detected && detected.fields[key];
        if (!f) { showToast('این فیلد در فرم فعلی پیدا نشد.', 'warn'); return; }

        var rw = document.getElementById('ac-rewrite');
        var isRewrite = !!(rw && rw.checked);

        // ★ v1.6.0 — در حالت بازنویسی، متن فعلی همان فیلد منبع است
        var extra = null;
        if (isRewrite) {
            var cur = readText(f);
            if (!cur || cur.trim().length < 10) {
                showToast('این فیلد خالی/خیلی کوتاه است — برای بازنویسی متن موجود لازم است؛ چک‌باکس را بردارید تا از صفر تولید شود.', 'warn');
                return;
            }
            extra = { mode: 'rewrite', source: String(cur).slice(0, 8000) };
        }

        var topic = (document.getElementById('ac-topic').value || pageTopic() || (f.el.value || '')).trim();
        if (!topic && !isRewrite) { showToast('ابتدا «موضوع/عنوان» را بنویسید.', 'warn'); return; }

        var btn = panel.querySelector('[data-gen="' + key + '"]');
        callAssistant(key, topic, null, function (text) {
            applyText(f, text);
            recordHistory(key, f.label || FIELD_LABELS[key], topic, text);
            showResult(text, f, (isRewrite ? 'بازنویسی شد و درج شد در «' : 'درج شد در «') + (f.label || FIELD_LABELS[key]) + '» ✓', { key: key, topic: topic });
            renderSeo();
        }, btnBusy(btn), extra);
    }

    function generateAll() {
        if (!detected) { return; }
        var keys = Object.keys(detected.fields);
        if (!keys.length) { return; }

        var allBtn = document.getElementById('ac-gen-all');
        var busy = btnBusy(allBtn);
        busy.on();

        var rw = document.getElementById('ac-rewrite');
        var isRewrite = !!(rw && rw.checked);
        showToast(isRewrite
            ? 'بازنویسی ' + faNum(keys.length) + ' فیلد آغاز شد…'
            : 'تولید ' + faNum(keys.length) + ' فیلد آغاز شد…');

        var i = 0;
        function next() {
            if (i >= keys.length) {
                busy.off();
                showToast('همهٔ فیلدها آماده و درج شد ✓', 'ok');
                renderSeo();
                return;
            }
            var key = keys[i++];
            var f = detected.fields[key];
            var topic = (document.getElementById('ac-topic').value || pageTopic() || '').trim();

            // ★ v1.6.0 — در بازنویسی گروهی، متن فعلی هر فیلد منبع است
            var extra = null;
            if (isRewrite) {
                var cur = readText(f);
                if (cur && String(cur).trim().length >= 10) {
                    extra = { mode: 'rewrite', source: String(cur).slice(0, 8000) };
                } else {
                    next(); // فیلد خالی — رد می‌شود
                    return;
                }
            } else if (!topic) {
                busy.off(); showToast('ابتدا «موضوع/عنوان» را بنویسید.', 'warn'); return;
            }

            var chipBtn = panel.querySelector('[data-gen="' + key + '"]');
            if (chipBtn) { chipBtn.classList.add('busy'); }

            assistCall(payloadFor(key, isRewrite ? '' : topic, null, extra), function (d) {
                if (chipBtn) { chipBtn.classList.remove('busy'); chipBtn.classList.add('done'); }
                var text = String(d.text || '');
                applyText(f, text);
                recordHistory(key, f.label || FIELD_LABELS[key], topic, text);
                showResult(text, f, 'درج شد در «' + (f.label || FIELD_LABELS[key]) + '» ✓', { key: key, topic: topic });
                next();
            }, function (msg) {
                if (chipBtn) { chipBtn.classList.remove('busy'); }
                showToast(msg, 'err');
                next();
            });
        }
        next();
    }

    /* ---------- نمایش نتیجه (+ شمارش کلمه + تولید مجدد) ---------- */
    function showResult(text, f, note, genInfo) {
        var box = document.getElementById('ac-result');
        if (!box) { return; }

        if (genInfo) { lastGen = genInfo; }

        var isHtml = /<(p|h[1-6]|ul|ol|li|strong|em|table)\b/i.test(text);
        var wc = wordCount(text);
        var canRegen = !!(lastGen && ASSIST_ON);

        box.hidden = false;
        box.innerHTML =
            '<div class="ac-res-note">' + esc(note || '') + '</div>' +
            '<div class="ac-res-text' + (isHtml ? ' html' : '') + '">' + (isHtml ? text : esc(text)) + '</div>' +
            '<div class="ac-res-meta">' +
            '<span class="ac-res-words">' + faNum(wc) + ' کلمه · ' + faNum(text.length) + ' کاراکتر</span>' +
            '<span class="ac-res-actions">' +
            (canRegen ? '<button type="button" class="ac-res-btn" id="ac-res-regen">' + SVG.again + ' تولید مجدد</button>' : '') +
            (f ? '<button type="button" class="ac-res-btn" id="ac-res-reapply">درج مجدد در فرم</button>' : '') +
            '<button type="button" class="ac-res-btn" id="ac-res-copy">' + SVG.copy + ' کپی</button>' +
            '</span>' +
            '</div>';

        var rg = document.getElementById('ac-res-regen');
        if (rg && lastGen) {
            rg.addEventListener('click', function () {
                if (lastGen.key === 'custom') {
                    var ask = lastGen.topic;
                    callAssistant('custom', ask, null, function (text2) {
                        recordHistory('custom', 'متن آزاد', ask, text2);
                        showResult(text2, null, 'متن تولیدشده (کپی کنید):', { key: 'custom', topic: ask });
                    }, btnBusy(rg));
                } else if (lastGen.key === 'reply') {
                    callAssistant('reply', null, lastGen.topic, function (text2) {
                        recordHistory('reply', 'پاسخ کامنت', lastGen.topic, text2);
                        var rf = detected && detected.fields.reply ? detected.fields.reply : null;
                        if (rf) { applyText(rf, text2); }
                        showResult(text2, rf, rf ? 'درج شد در فیلد پاسخ ✓' : 'پاسخ تولیدشده (کپی کنید):', { key: 'reply', topic: lastGen.topic });
                    }, btnBusy(rg));
                } else {
                    generateField(lastGen.key);
                }
            });
        }
        var ra = document.getElementById('ac-res-reapply');
        if (ra && f) {
            ra.addEventListener('click', function () { applyText(f, text); });
        }
        var cp = document.getElementById('ac-res-copy');
        if (cp) {
            cp.addEventListener('click', function () { copyText(text); });
        }
    }

    /* ---------- پیام کوتاه (toast) ---------- */
    var toastBox = null;

    function showToast(msg, kind) {
        if (!toastBox) {
            toastBox = document.createElement('div');
            toastBox.className = 'ac-toast-box';
            document.body.appendChild(toastBox);
        }
        var t = document.createElement('div');
        t.className = 'ac-toast ' + (kind || 'info');
        t.textContent = msg;
        toastBox.appendChild(t);
        setTimeout(function () {
            t.classList.add('out');
            setTimeout(function () { if (t.parentNode) { t.parentNode.removeChild(t); } }, 350);
        }, kind === 'err' ? 6000 : 3200);
    }

    /* ================================================================
     *  بوت
     * ================================================================ */
    function boot() {
        initAssistant();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
