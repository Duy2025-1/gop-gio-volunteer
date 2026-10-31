/**
 * Góp Gió Volunteer – Frontend UI & Micro-interactions
 * - Lucide icons initialization
 * - Accessible modal confirmation before critical actions
 * - Form submit button loading state
 * - Toast notifications & Notification polling
 */

const GopGioApp = {
    pendingConfirmForm: null,

    init() {
        this.initLucideIcons();
        this.initConfirmForms();
        this.initFormLoadingState();
        this.initNotificationPolling();
    },

    initLucideIcons() {
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    },

    showToast(message, type = 'success') {
        const container = document.getElementById('ggToastContainer');
        if (!container) return;

        const colorMap = {
            success: '#15803D',
            error: '#B91C1C',
            warning: '#D97706',
            info: '#1E293B'
        };

        const toastEl = document.createElement('div');
        toastEl.className = 'gg-card p-3 mb-2 d-flex align-items-start justify-content-between gap-2 shadow-sm';
        toastEl.style.borderLeft = `4px solid ${colorMap[type] || colorMap.success}`;
        toastEl.style.backgroundColor = '#FFFFFF';
        toastEl.innerHTML = `
            <div class="small fw-medium text-dark">${this.escapeHtml(message)}</div>
            <button type="button" class="btn-close btn-sm" aria-label="Đóng"></button>
        `;

        toastEl.querySelector('.btn-close').addEventListener('click', () => toastEl.remove());
        container.appendChild(toastEl);

        setTimeout(() => {
            if (toastEl && toastEl.parentNode) {
                toastEl.remove();
            }
        }, 4500);
    },

    initConfirmForms() {
        const modalEl = document.getElementById('ggConfirmModal');
        const confirmBtn = document.getElementById('ggConfirmModalAccept');
        const msgEl = document.getElementById('ggConfirmModalMessage');

        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                if (this.pendingConfirmForm) {
                    const formToSubmit = this.pendingConfirmForm;
                    this.pendingConfirmForm = null;
                    formToSubmit.removeAttribute('data-confirm');
                    formToSubmit.submit();
                }
            });
        }

        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (e) => {
                const msg = form.getAttribute('data-confirm');
                if (!msg) return;

                if (modalEl && typeof bootstrap !== 'undefined') {
                    e.preventDefault();
                    this.pendingConfirmForm = form;
                    if (msgEl) msgEl.textContent = msg;
                    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    bsModal.show();
                } else if (!window.confirm(msg)) {
                    e.preventDefault();
                }
            });
        });
    },

    initFormLoadingState() {
        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', () => {
                if (form.hasAttribute('data-confirm')) return;
                const btn = form.querySelector('button[type="submit"]');
                if (btn && !btn.dataset.loadingApplied) {
                    btn.dataset.loadingApplied = '1';
                    btn.style.opacity = '0.85';
                }
            });
        });
    },

    initNotificationPolling() {
        const badge = document.getElementById('ggUnreadBadge');
        if (!badge) return;

        const fetchNotifications = async () => {
            try {
                const res = await fetch('/api/notifications/unread', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success) return;

                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }

                const listContainer = document.getElementById('ggHeaderNotifList');
                if (listContainer && Array.isArray(data.items)) {
                    if (data.items.length === 0) {
                        listContainer.innerHTML = '<div class="p-3 text-center text-muted small">Chưa có thông báo mới.</div>';
                    } else {
                        listContainer.innerHTML = data.items.slice(0, 5).map(item => `
                            <a href="${this.escapeHtml(item.link_url || '#')}" class="dropdown-item py-2 border-bottom text-wrap ${Number(item.is_read) === 0 ? 'bg-light fw-medium' : ''}">
                                <div class="small text-dark">${this.escapeHtml(item.title)}</div>
                                <div class="text-muted" style="font-size:0.78rem;">${this.escapeHtml(item.message)}</div>
                            </a>
                        `).join('');
                    }
                }
            } catch (err) {
                // Ignore transient polling error
            }
        };

        fetchNotifications();
        setInterval(fetchNotifications, 25000);
    },

    fillDemoAccount(email, password) {
        const emailInput = document.getElementById('loginEmail');
        const passInput = document.getElementById('loginPassword');
        if (emailInput && passInput) {
            emailInput.value = email;
            passInput.value = password;
            this.showToast(`Đã điền tài khoản mẫu: ${email}`, 'info');
        }
    },

    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }
};

document.addEventListener('DOMContentLoaded', () => GopGioApp.init());
