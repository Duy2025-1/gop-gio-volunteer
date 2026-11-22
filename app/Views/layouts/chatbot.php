<?php
/**
 * Gió AI – Trợ lý tình nguyện viên (Frontend Chat Widget Partial)
 */
?>
<div id="gioAiWidget" class="gio-ai-widget" aria-live="polite">
    <!-- Nút Chat Nổi (Góc dưới bên phải) -->
    <button type="button"
            id="gioAiToggleBtn"
            class="gio-ai-launcher"
            aria-label="Mở khung trò chuyện Gió AI – Trợ lý tình nguyện viên"
            aria-expanded="false"
            aria-controls="gioAiPanel">
        <span class="gio-ai-launcher-icon" aria-hidden="true">
            <i data-lucide="sparkles" style="width:20px;height:20px;"></i>
        </span>
        <span class="gio-ai-launcher-label">Gió AI</span>
    </button>

    <!-- Khung Trò Chuyện Gió AI -->
    <section id="gioAiPanel"
             class="gio-ai-panel d-none"
             role="dialog"
             aria-label="Gió AI – Trợ lý tình nguyện viên">
        <!-- Header -->
        <header class="gio-ai-header">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <div class="gio-ai-avatar" aria-hidden="true">
                    <i data-lucide="sprout" style="width:18px;height:18px;"></i>
                </div>
                <div class="min-w-0">
                    <div class="gio-ai-title text-truncate">Gió AI – Trợ lý tình nguyện viên</div>
                    <div class="gio-ai-status">
                        <span class="gio-ai-status-dot"></span>
                        <span id="gioAiStatusText">Đang hỗ trợ</span>
                    </div>
                </div>
            </div>
            <div class="gio-ai-header-actions">
                <button type="button"
                        id="gioAiClearBtn"
                        class="gio-ai-icon-btn"
                        title="Bắt đầu cuộc trò chuyện mới / Xóa lịch sử"
                        aria-label="Xóa lịch sử trò chuyện">
                    <i data-lucide="rotate-ccw" style="width:15px;height:15px;"></i>
                </button>
                <button type="button"
                        id="gioAiMinimizeBtn"
                        class="gio-ai-icon-btn"
                        title="Thu nhỏ cửa sổ chat"
                        aria-label="Thu nhỏ cửa sổ chat">
                    <i data-lucide="minus" style="width:16px;height:16px;"></i>
                </button>
                <button type="button"
                        id="gioAiCloseBtn"
                        class="gio-ai-icon-btn"
                        title="Đóng cửa sổ chat"
                        aria-label="Đóng cửa sổ chat">
                    <i data-lucide="x" style="width:16px;height:16px;"></i>
                </button>
            </div>
        </header>

        <!-- Phần Nội Dung Tin Nhắn -->
        <div id="gioAiMessages" class="gio-ai-body">
            <!-- Màn hình chào mừng & Câu hỏi gợi ý (Hiển thị mặc định và khi tạo hội thoại mới) -->
            <div id="gioAiWelcomeBox" class="gio-ai-welcome">
                <div class="gio-ai-msg gio-ai-msg-bot">
                    <div class="gio-ai-msg-avatar" aria-hidden="true">
                        <i data-lucide="sprout" style="width:14px;height:14px;"></i>
                    </div>
                    <div class="gio-ai-msg-content">
                        <div class="gio-ai-bubble">
                            <p class="mb-2">Xin chào! Mình là <strong>Gió AI</strong> – trợ lý của <strong>Góp Gió Volunteer</strong>.</p>
                            <p class="mb-0">Bạn đang cần hỗ trợ điều gì hôm nay? Mình có thể giúp bạn tìm hiểu các chương trình tình nguyện, hướng dẫn đăng ký tham gia hoặc giải đáp những thắc mắc của bạn.</p>
                        </div>
                        <div class="gio-ai-msg-time">Hôm nay</div>
                    </div>
                </div>

                <div id="gioAiSuggestions" class="gio-ai-suggestions">
                    <div class="gio-ai-suggestions-title">Câu hỏi gợi ý dành cho bạn:</div>
                    <div class="gio-ai-suggestions-list">
                        <button type="button" class="gio-ai-suggestion-item" data-question="Làm sao để đăng ký tham gia tình nguyện?">
                            Làm sao để đăng ký tham gia tình nguyện?
                        </button>
                        <button type="button" class="gio-ai-suggestion-item" data-question="Làm sao để điểm danh bằng mã QR?">
                            Làm sao để điểm danh bằng mã QR?
                        </button>
                        <button type="button" class="gio-ai-suggestion-item" data-question="Tôi có thể xem lịch sử hoạt động tình nguyện ở đâu?">
                            Tôi có thể xem lịch sử hoạt động tình nguyện ở đâu?
                        </button>
                        <button type="button" class="gio-ai-suggestion-item" data-question="Làm thế nào để nhận giấy chứng nhận?">
                            Làm thế nào để nhận giấy chứng nhận?
                        </button>
                        <button type="button" class="gio-ai-suggestion-item" data-question="Hiện tại có những chương trình tình nguyện nào?">
                            Hiện tại có những chương trình tình nguyện nào?
                        </button>
                    </div>
                </div>
            </div>

            <!-- Danh sách tin nhắn hội thoại -->
            <div id="gioAiConversationList" class="gio-ai-conversation"></div>

            <!-- Hiệu ứng Gió AI đang suy nghĩ -->
            <div id="gioAiThinking" class="gio-ai-msg gio-ai-msg-bot d-none">
                <div class="gio-ai-msg-avatar" aria-hidden="true">
                    <i data-lucide="sprout" style="width:14px;height:14px;"></i>
                </div>
                <div class="gio-ai-msg-content">
                    <div class="gio-ai-bubble gio-ai-typing-bubble">
                        <span class="gio-ai-typing-dots">
                            <span></span><span></span><span></span>
                        </span>
                        <span class="gio-ai-typing-label">Gió AI đang suy nghĩ...</span>
                    </div>
                </div>
            </div>

            <!-- Thông báo lỗi & Nút thử gửi lại -->
            <div id="gioAiErrorBox" class="gio-ai-error d-none" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;margin-top:2px;"></i>
                    <div class="flex-grow-1">
                        <div id="gioAiErrorText" class="small">Không thể kết nối tới Gió AI.</div>
                        <button type="button" id="gioAiRetryBtn" class="gio-ai-retry-btn mt-1 d-none">
                            Thử gửi lại
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần Nhập Tin Nhắn -->
        <footer class="gio-ai-footer">
            <form id="gioAiForm" class="gio-ai-form" novalidate>
                <div class="gio-ai-input-wrap">
                    <textarea id="gioAiInput"
                              class="gio-ai-input"
                              rows="1"
                              maxlength="500"
                              placeholder="Nhập câu hỏi cho Gió AI... (Enter để gửi)"
                              aria-label="Nhập tin nhắn gửi Gió AI"></textarea>
                    <button type="submit"
                            id="gioAiSendBtn"
                            class="gio-ai-send-btn"
                            aria-label="Gửi tin nhắn"
                            disabled>
                        <i data-lucide="send" style="width:16px;height:16px;"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                    <span class="gio-ai-hint">Enter để gửi • Shift + Enter xuống dòng</span>
                    <span id="gioAiCharCount" class="gio-ai-char-count">0/500</span>
                </div>
            </form>
        </footer>
    </section>
</div>
