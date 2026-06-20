// robots.js

// ── Mode Selector ──────────────────────────────────────────────
function selectMode(el, mode) {
    document.querySelectorAll('.rb-mode-card').forEach(c => {
        c.classList.remove('selected', 'selected-warn', 'selected-danger');
    });
    const cls = mode === 'production' ? 'selected' : (mode === 'development' ? 'selected-warn' : 'selected-danger');
    el.classList.add(cls);
    el.querySelector('input').checked = true;

    const labels = {
        production: '🟢 حالت تولید',
        development: '🟡 حالت توسعه',
        disabled: '🔴 غیرفعال',
    };
    document.getElementById('current-mode-badge').textContent = labels[mode];
    livePreview();
}

// ── Slider ────────────────────────────────────────────────────
function updateSlider(input) {
    document.getElementById('slider-val').textContent = input.value + 's';
    const pct = (input.value / input.max * 100) + '%';
    input.style.setProperty('--slider-pct', pct);
    input.style.background = `linear-gradient(to right, var(--rb-primary) 0%, var(--rb-primary) ${pct}, var(--rb-border) ${pct})`;
    livePreview();
}

// ── Add / Remove Path ─────────────────────────────────────────
function addPath(containerId, name, prefix) {
    const container = document.getElementById(containerId);
    const row = document.createElement('div');
    row.className = 'rb-path-row';
    row.innerHTML = `
        <span class="rb-path-prefix">${prefix}</span>
        <input type="text" name="${name}" class="rb-path-input" placeholder="/path/" oninput="livePreview()">
        <button type="button" class="rb-path-del" onclick="removePath(this)" title="حذف">✕</button>`;
    container.appendChild(row);
    row.querySelector('input').focus();
}

function quickAdd(containerId, name, prefix, value) {
    const container = document.getElementById(containerId);
    // بررسی تکراری نبودن
    const existing = [...container.querySelectorAll('input')].map(i => i.value);
    if (existing.includes(value)) {
        showToast('⚠️', 'این مسیر قبلاً اضافه شده');
        return;
    }
    const row = document.createElement('div');
    row.className = 'rb-path-row';
    row.innerHTML = `
        <span class="rb-path-prefix">${prefix}</span>
        <input type="text" name="${name}" class="rb-path-input" value="${value}" oninput="livePreview()">
        <button type="button" class="rb-path-del" onclick="removePath(this)" title="حذف">✕</button>`;
    container.appendChild(row);
    livePreview();
}

function removePath(btn) {
    btn.closest('.rb-path-row').style.opacity = '0';
    setTimeout(() => {
        btn.closest('.rb-path-row').remove();
        livePreview();
    }, 150);
}

// ── Live Preview ──────────────────────────────────────────────
function getFormData() {
    const form = document.getElementById('robots-form');
    const fd = new FormData(form);
    // دریافت action از attribute داده
    const previewUrl = ROBOTS_URLS.preview || '/admin/robots/preview';
    const updateUrl = ROBOTS_URLS.update || '/admin/robots/update';

    return {
        mode: fd.get('mode'),
        crawl_delay: fd.get('crawl_delay'),
        sitemap: fd.get('sitemap'),
        disallow: fd.getAll('disallow[]').filter(p => p.trim()),
        allow: fd.getAll('allow[]').filter(p => p.trim()),
        previewUrl: previewUrl,
        updateUrl: updateUrl,
    };
}

function livePreview() {
    const data = getFormData();
    const previewUrl = data.previewUrl;
    delete data.previewUrl;
    delete data.updateUrl;

    fetch(previewUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
        .then(r => r.json())
        .then(res => {
            const el = document.getElementById('robots-preview');
            el.innerHTML = syntaxHighlight(res.content);
        })
        .catch(err => {
            console.error('Preview error:', err);
        });
}

function syntaxHighlight(code) {
    return code
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/(#[^\n]*)/g, '<span class="c">$1</span>')
        .replace(/^(User-agent|Disallow|Allow|Crawl-delay|Sitemap):/gm, '<span class="k">$1</span>:')
        .replace(/:\s*(\/[^\n]*)/g, ': <span class="v">$1</span>')
        .replace(/:\s*(https?:\/\/[^\n]*)/g, ': <span class="u">$1</span>');
}

// ── Copy Preview ─────────────────────────────────────────────
function copyPreview() {
    const text = document.getElementById('robots-preview').textContent;
    navigator.clipboard.writeText(text).then(() => showToast('📋', 'محتوا کپی شد'));
}

// ── Save Form ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('robots-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const data = getFormData();
        const updateUrl = data.updateUrl;
        delete data.previewUrl;
        delete data.updateUrl;

        const btn = this.querySelector('[type=submit]');
        btn.disabled = true;
        btn.textContent = '⏳ در حال ذخیره...';

        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.textContent = '💾 ذخیره و اعمال';
                if (res.success) {
                    showToast('✅', res.message || 'تنظیمات با موفقیت ذخیره شد');
                    const status = document.getElementById('save-status');
                    if (status) {
                        status.className = 'rb-save-status ok';
                        status.innerHTML = '✅ ذخیره‌شده — ' + new Date().toLocaleTimeString('fa-IR');
                    }
                } else {
                    showToast('❌', 'خطا در ذخیره تنظیمات');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.textContent = '💾 ذخیره و اعمال';
                showToast('❌', 'خطا در ارتباط با سرور');
            });
    });

    // مقداردهی اولیه
    livePreview();
    const slider = document.getElementById('crawl-delay-slider');
    if (slider) {
        updateSlider(slider);
    }
});

// ── Toast ─────────────────────────────────────────────────────
function showToast(icon, msg) {
    const t = document.getElementById('rb-toast');
    if (!t) {
        // اگر toast وجود نداشت، با alert ساده
        alert(icon + ' ' + msg);
        return;
    }
    document.getElementById('rb-toast-icon').textContent = icon;
    document.getElementById('rb-toast-msg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}
