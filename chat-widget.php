<?php
if (!defined('ABSPATH')) exit;

// In khung chat vào Footer của website
add_action('wp_footer', 'bizmatch_bot_render_chat_widget');

function bizmatch_bot_render_chat_widget() {
    $bot_name   = get_option('bizmatch_bot_name', 'Trợ Lý AI');
    $position   = get_option('bizmatch_chat_position', 'right');
    $zalo_phone = get_option('bizmatch_zalo_phone', '');
    $site_name  = get_bloginfo('name'); // Lấy tên website tự động
    $pos_class  = ($position === 'left') ? 'bizmatch-position-left' : 'bizmatch-position-right';
    ?>
    
    <!-- Nút Bật Chat Float -->
    <div id="bizmatch-chat-toggle" class="<?php echo esc_attr($pos_class); ?>">
        <span class="bizmatch-icon">💬</span>
    </div>

    <!-- Khung Chat Widget -->
    <div id="bizmatch-chat-container" class="<?php echo esc_attr($pos_class); ?>" style="display: none;">
        <!-- Header -->
        <div class="bizmatch-chat-header">
            <div class="bizmatch-chat-title">
                <span class="bizmatch-header-icon">🤖</span>
                <strong><?php echo esc_html($bot_name); ?></strong>
            </div>
            <button id="bizmatch-chat-close" type="button">&times;</button>
        </div>

        <!-- Messages Area (ĐÃ THÊM ĐOẠN CODE NÚT ZALO VÀO ĐÂY) -->
        <div class="bizmatch-chat-messages" id="bizmatch-chat-messages">
            <div class="bizmatch-message bizmatch-bot-message">
                <p>Xin chào! Tôi là <?php echo esc_html($bot_name); ?>. Tôi có thể giúp gì cho bạn?</p>
                
                <?php if (!empty($zalo_phone)): ?>
                    <div class="bizmatch-zalo-option">
                        <p style="margin-top: 8px; margin-bottom: 6px; font-size: 13px; color: #555;">Hoặc cần hỗ trợ nhanh hơn?</p>
                        <a href="https://zalo.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', $zalo_phone)); ?>" target="_blank" rel="nofollow" class="bizmatch-zalo-btn">
                            💬 Liên hệ trực tiếp với <?php echo esc_html($site_name); ?> qua Zalo
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Input -->
        <div class="bizmatch-chat-footer">
            <input type="text" id="bizmatch-chat-input" placeholder="Nhập câu hỏi..." autocomplete="off" />
            <button id="bizmatch-chat-send" type="button">Gửi</button>
        </div>
    </div>
    <?php
}