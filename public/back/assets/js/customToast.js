const TOAST_MAX = 5;
let toastList = null;

function getToastList() {
    if (!toastList || !document.body.contains(toastList)) {
        toastList = document.createElement('div');
        toastList.id = 'toast-list';
        toastList.style.cssText = `
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 9999;
            pointer-events: none;
            max-width: 90vw;
        `;
        document.body.appendChild(toastList);
    }
    return toastList;
}

function showCustomToast(message, type = 'info', duration = 5000) {
    const list = getToastList();

    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };

    const toast = document.createElement('div');
    toast.className = 'toast-item'; // اسم جدید — با CSS قدیمی تداخل نداره

    toast.style.cssText = `
        position: relative;      /* مهم: نه fixed */
        background: #1a1a2e;
        color: #fff;
        padding: .7rem 1.4rem;
        border-radius: 6px;
        font-size: .8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: .5rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, .2);
        opacity: 0;
        transform: translateY(20px);
        transition: all .3s ease;
        pointer-events: auto;
        border-right: 4px solid ${colors[type] || colors.info};
        min-width: 200px;
        max-width: 500px;
        margin-top: .5rem;
        box-sizing: border-box;
        cursor: pointer;
    `;

    toast.innerHTML = `
        <span>${icons[type] || 'ℹ️'}</span>
        <span>${message}</span>
        <button class="toast-item-close" style="
            background: none;
            border: none;
            color: #fff;
            opacity: .6;
            cursor: pointer;
            font-size: 1.2rem;
            line-height: 1;
            padding: 0 0 0 .5rem;
        ">&times;</button>
    `;

    // اگه از سقف بیشتر شد، قدیمی‌ترین حذف بشه
    while (list.children.length >= TOAST_MAX) {
        list.firstElementChild.remove();
    }

    list.appendChild(toast);

    // انیمیشن ورود
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });
    });

    // تایمر بسته‌شدن خودکار
    let timer = null;
    if (duration > 0) {
        timer = setTimeout(() => hideCustomToast(toast), duration);
    }

    const close = () => {
        clearTimeout(timer);
        hideCustomToast(toast);
    };

    toast.querySelector('.toast-item-close').addEventListener('click', (e) => {
        e.stopPropagation();
        close();
    });

    toast.addEventListener('click', (e) => {
        if (e.target === toast || e.target.tagName === 'SPAN') close();
    });
}

function hideCustomToast(toast) {
    if (!toast || toast.dataset.closing) return;
    toast.dataset.closing = '1';

    // جمع‌شدن نرم ارتفاع تا بقیه‌ی لیست نپره
    toast.style.height = toast.offsetHeight + 'px';
    toast.style.overflow = 'hidden';
    void toast.offsetHeight; // force reflow

    toast.style.opacity = '0';
    toast.style.height = '0';
    toast.style.marginTop = '0';
    toast.style.paddingTop = '0';
    toast.style.paddingBottom = '0';

    setTimeout(() => {
        toast.remove();
        const list = document.getElementById('toast-list');
        if (list && list.children.length === 0) {
            list.remove();
            toastList = null;
        }
    }, 300);
}
