/**
 * Gió AI – Trợ lý tình nguyện viên (Frontend Chat Widget Module)
 * Tích hợp trực tiếp với Backend API (/api/chat/history, /api/chat/send, /api/chat/clear)
 */

const GioAiChatbot = {
    isOpen: false,
    isLoadedHistory: false,
    isSending: false,
    maxLength: 500,
    lastSentAt: 0,
    lastFailedMessage: null,

    elements: {},

    init() {
        this.cacheElements();
        if (!this.elements.widget) return;

        this.bindEvents();

        // Khôi phục trạng thái mở khung chat trong phiên làm việc nếu người dùng đang mở
        if (sessionStorage.getItem('gio_ai_panel_open') === '1') {
            this.openPanel();
        }
    },

    cacheElements() {
        this.elements = {
            widget: document.getElementById('gioAiWidget'),
            toggleBtn: document.getElementById('gioAiToggleBtn'),
            panel: document.getElementById('gioAiPanel'),
            minimizeBtn: document.getElementById('gioAiMinimizeBtn'),
            closeBtn: document.getElementById('gioAiCloseBtn'),
            clearBtn: document.getElementById('gioAiClearBtn'),
            messagesBox: document.getElementById('gioAiMessages'),
            welcomeBox: document.getElementById('gioAiWelcomeBox'),
            suggestionsBox: document.getElementById('gioAiSuggestions'),
            conversationList: document.getElementById('gioAiConversationList'),
            thinkingBox: document.getElementById('gioAiThinking'),
            errorBox: document.getElementById('gioAiErrorBox'),
            errorText: document.getElementById('gioAiErrorText'),
            retryBtn: document.getElementById('gioAiRetryBtn'),
            form: document.getElementById('gioAiForm'),
            input: document.getElementById('gioAiInput'),
            sendBtn: document.getElementById('gioAiSendBtn'),
            charCount: document.getElementById('gioAiCharCount'),
            statusText: document.getElementById('gioAiStatusText'),
        };
    },

    bindEvents() {
        const el = this.elements;

        el.toggleBtn?.addEventListener('click', () => this.openPanel());
        el.minimizeBtn?.addEventListener('click', () => this.closePanel());
        el.closeBtn?.addEventListener('click', () => this.closePanel());
        el.clearBtn?.addEventListener('click', () => this.clearHistory());

        // Câu hỏi gợi ý
        el.widget.querySelectorAll('.gio-ai-suggestion-item').forEach((btn) => {
            btn.addEventListener('click', () => {
                const question = btn.getAttribute('data-question') || btn.textContent.trim();
                if (question && !this.isSending) {
                    this.sendMessage(question);
                }
            });
        });

        // Ô nhập tin nhắn: tự động co giãn, đếm ký tự, Enter để gửi, Shift+Enter xuống dòng
        el.input?.addEventListener('input', () => {
            this.updateInputState();
        });

        el.input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.handleFormSubmit();
            }
        });

        el.form?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleFormSubmit();
        });

        // Nút thử gửi lại khi lỗi
        el.retryBtn?.addEventListener('click', () => {
            if (this.lastFailedMessage && !this.isSending) {
                const msgToRetry = this.lastFailedMessage;
                this.hideError();
                this.sendMessage(msgToRetry, true);
            }
        });

        // Theo dõi trạng thái mạng
        window.addEventListener('offline', () => {
            if (el.statusText) el.statusText.textContent = 'Mất kết nối mạng';
            this.showError('Thiết bị của bạn đang mất kết nối mạng. Vui lòng kiểm tra lại kết nối Internet.', false);
        });

        window.addEventListener('online', () => {
            if (el.statusText) el.statusText.textContent = 'Đang hỗ trợ';
            this.hideError();
        });
    },

    openPanel() {
        const el = this.elements;
        this.isOpen = true;
        el.panel?.classList.remove('d-none');
        el.toggleBtn?.classList.add('d-none');
        el.toggleBtn?.setAttribute('aria-expanded', 'true');
        sessionStorage.setItem('gio_ai_panel_open', '1');

        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }

        if (!this.isLoadedHistory) {
            this.loadHistory();
        } else {
            this.scrollToBottom();
            el.input?.focus();
        }
    },

    closePanel() {
        const el = this.elements;
        this.isOpen = false;
        el.panel?.classList.add('d-none');
        el.toggleBtn?.classList.remove('d-none');
        el.toggleBtn?.setAttribute('aria-expanded', 'false');
        sessionStorage.setItem('gio_ai_panel_open', '0');
    },

    updateInputState() {
        const el = this.elements;
        if (!el.input) return;

        const val = el.input.value;
        const len = val.length;
        if (el.charCount) {
            el.charCount.textContent = `${len}/${this.maxLength}`;
        }

        el.input.style.height = 'auto';
        el.input.style.height = Math.min(el.input.scrollHeight, 92) + 'px';

        if (el.sendBtn) {
            el.sendBtn.disabled = this.isSending || val.trim().length === 0 || len > this.maxLength;
        }
    },

    async loadHistory() {
        this.isLoadedHistory = true;
        try {
            const res = await fetch('/api/chat/history', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const json = await res.json();
            if (!json.success || !json.data) return;

            if (json.data.max_message_length) {
                this.maxLength = Number(json.data.max_message_length) || 500;
            }

            const history = Array.isArray(json.data.history) ? json.data.history : [];
            if (history.length > 0 && this.elements.conversationList) {
                this.elements.conversationList.innerHTML = '';
                history.forEach((msg) => {
                    this.appendMessageBubble(msg.role === 'user' ? 'user' : 'bot', msg.content, msg.time || '');
                });
            }
            this.scrollToBottom();
            this.elements.input?.focus();
        } catch (err) {
            // Giữ màn hình chào mừng mặc định nếu chưa tải được lịch sử
        }
    },

    handleFormSubmit() {
        const el = this.elements;
        if (!el.input || this.isSending) return;

        const text = el.input.value.trim();
        if (text === '') {
            this.showError('Vui lòng nhập nội dung câu hỏi trước khi gửi.', false);
            return;
        }

        if (text.length > this.maxLength) {
            this.showError(`Tin nhắn không được vượt quá ${this.maxLength} ký tự.`, false);
            return;
        }

        // Chống bấm gửi liên tục trong < 800ms
        const now = Date.now();
        if (now - this.lastSentAt < 800) {
            this.showError('Bạn đang thao tác quá nhanh, vui lòng đợi một chút nhé.', false);
            return;
        }

        el.input.value = '';
        this.updateInputState();
        this.sendMessage(text, false);
    },

    async sendMessage(messageText, isRetry = false) {
        const el = this.elements;
        if (this.isSending) return;

        if (!navigator.onLine) {
            this.lastFailedMessage = messageText;
            this.showError('Mất kết nối Internet. Vui lòng kiểm tra mạng và nhấn "Thử gửi lại".', true);
            return;
        }

        this.hideError();
        this.isSending = true;
        this.lastSentAt = Date.now();
        this.updateInputState();

        const currentTime = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        if (!isRetry) {
            this.appendMessageBubble('user', messageText, currentTime);
        }

        // Ẩn danh sách gợi ý sau khi bắt đầu hội thoại để khung chat gọn gàng
        el.suggestionsBox?.classList.add('d-none');
        this.setThinking(true);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 18000);

            const res = await fetch('/api/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    message: messageText
                }),
                signal: controller.signal
            });

            clearTimeout(timeoutId);
            const data = await res.json().catch(() => null);

            if (!res.ok || !data || !data.success) {
                const errMsg = data?.message || 'Gió AI tạm thời không phản hồi. Vui lòng nhấn "Thử gửi lại".';
                const canRetry = data ? Boolean(data.retryable) : true;
                this.lastFailedMessage = canRetry ? messageText : null;
                this.showError(errMsg, canRetry);
                return;
            }

            this.lastFailedMessage = null;
            this.appendMessageBubble('bot', data.reply, data.timestamp || currentTime);
        } catch (err) {
            this.lastFailedMessage = messageText;
            const isTimeout = err && err.name === 'AbortError';
            this.showError(
                isTimeout
                    ? 'Yêu cầu tới Gió AI đã quá thời gian chờ. Vui lòng nhấn "Thử gửi lại".'
                    : 'Không thể kết nối tới máy chủ Gió AI. Vui lòng kiểm tra kết nối và thử lại.',
                true
            );
        } finally {
            this.setThinking(false);
            this.isSending = false;
            this.updateInputState();
            el.input?.focus();
        }
    },

    async clearHistory() {
        if (this.isSending) return;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const res = await fetch('/api/chat/clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ csrf_token: csrfToken })
            });

            if (res.ok) {
                if (this.elements.conversationList) {
                    this.elements.conversationList.innerHTML = '';
                }
                this.elements.suggestionsBox?.classList.remove('d-none');
                this.hideError();
                this.lastFailedMessage = null;
                this.scrollToBottom();
            }
        } catch (err) {
            this.showError('Không thể xóa lịch sử lúc này. Vui lòng thử lại.', false);
        }
    },

    appendMessageBubble(sender, rawText, timeLabel) {
        const list = this.elements.conversationList;
        if (!list) return;

        const isBot = sender === 'bot';
        const wrapper = document.createElement('div');
        wrapper.className = `gio-ai-msg ${isBot ? 'gio-ai-msg-bot' : 'gio-ai-msg-user'}`;

        const formattedHtml = isBot
            ? this.renderMarkdown(rawText)
            : `<p class="mb-0">${this.escapeHtml(rawText).replace(/\n/g, '<br>')}</p>`;

        wrapper.innerHTML = `
            ${isBot ? `
                <div class="gio-ai-msg-avatar" aria-hidden="true">
                    <i data-lucide="sprout" style="width:14px;height:14px;"></i>
                </div>
            ` : ''}
            <div class="gio-ai-msg-content">
                <div class="gio-ai-bubble">${formattedHtml}</div>
                ${timeLabel ? `<div class="gio-ai-msg-time">${this.escapeHtml(timeLabel)}</div>` : ''}
            </div>
        `;

        list.appendChild(wrapper);
        if (isBot && typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons({ nodes: [wrapper] });
        }
        this.scrollToBottom();
    },

    setThinking(active) {
        const el = this.elements;
        if (!el.thinkingBox) return;
        if (active) {
            el.thinkingBox.classList.remove('d-none');
            if (el.statusText) el.statusText.textContent = 'Đang trả lời...';
        } else {
            el.thinkingBox.classList.add('d-none');
            if (el.statusText) el.statusText.textContent = 'Đang hỗ trợ';
        }
        this.scrollToBottom();
    },

    showError(message, showRetry = false) {
        const el = this.elements;
        if (!el.errorBox || !el.errorText) return;
        el.errorText.textContent = message;
        el.errorBox.classList.remove('d-none');
        if (el.retryBtn) {
            if (showRetry) {
                el.retryBtn.classList.remove('d-none');
            } else {
                el.retryBtn.classList.add('d-none');
            }
        }
        this.scrollToBottom();
    },

    hideError() {
        const el = this.elements;
        el.errorBox?.classList.add('d-none');
        el.retryBtn?.classList.add('d-none');
    },

    scrollToBottom() {
        const box = this.elements.messagesBox;
        if (!box) return;
        requestAnimationFrame(() => {
            box.scrollTop = box.scrollHeight;
        });
    },

    /**
     * Render Markdown cơ bản an toàn (chống XSS, hỗ trợ đậm, nghiêng, danh sách, đường dẫn nội bộ)
     */
    renderMarkdown(text) {
        if (!text) return '';
        const lines = String(text).split(/\r?\n/);
        let html = '';
        let inUl = false;
        let inOl = false;

        const closeLists = () => {
            if (inUl) { html += '</ul>'; inUl = false; }
            if (inOl) { html += '</ol>'; inOl = false; }
        };

        lines.forEach((rawLine) => {
            const trimmed = rawLine.trim();
            if (trimmed === '') {
                closeLists();
                return;
            }

            const ulMatch = trimmed.match(/^[-*+]\s+(.*)$/);
            const olMatch = trimmed.match(/^\d+\.\s+(.*)$/);

            if (ulMatch) {
                if (inOl) { html += '</ol>'; inOl = false; }
                if (!inUl) { html += '<ul class="mb-2">'; inUl = true; }
                html += `<li>${this.formatInlineMarkdown(ulMatch[1])}</li>`;
            } else if (olMatch) {
                if (inUl) { html += '</ul>'; inUl = false; }
                if (!inOl) { html += '<ol class="mb-2">'; inOl = true; }
                html += `<li>${this.formatInlineMarkdown(olMatch[1])}</li>`;
            } else {
                closeLists();
                html += `<p class="mb-2">${this.formatInlineMarkdown(trimmed)}</p>`;
            }
        });

        closeLists();
        return html;
    },

    formatInlineMarkdown(str) {
        let safe = this.escapeHtml(str);

        // Định dạng đường dẫn nội bộ trong cặp dấu `...` thành link bấm được
        safe = safe.replace(/`(\/[a-zA-Z0-9_\-\/\?=&]+)`/g, '<a href="$1">$1</a>');

        // Inline code `...`
        safe = safe.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Bold **...**
        safe = safe.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

        // Italic *...*
        safe = safe.replace(/\*([^*]+)\*/g, '<em>$1</em>');

        // Markdown link [label](/path)
        safe = safe.replace(/\[([^\]]+)\]\((\/[a-zA-Z0-9_\-\/\?=&]+)\)/g, '<a href="$2">$1</a>');

        return safe;
    },

    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }
};

document.addEventListener('DOMContentLoaded', () => GioAiChatbot.init());
