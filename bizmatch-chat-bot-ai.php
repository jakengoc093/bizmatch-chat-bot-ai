<?php
/**
 * Plugin Name: Bizmatch Chat Bot AI
 * Plugin URI:  https://ngocnguyen.com.vn/
 * Description: Chatbot Gemini AI tối ưu tốc độ & bảo mật cho WordPress.
 * Version:     2.2
 * Author:      Ngọc Nguyễn
 * Author URI:  https://ngocnguyen.com.vn/
 * Text Domain: bizmatch-chat-bot-ai
 */
// 1. Nhúng thư viện auto-update từ GitHub
require_once plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// 2. Khởi tạo đối tượng kiểm tra phiên bản
$bizmatch_update_checker = PucFactory::buildUpdateChecker(
    'https://github.com/jakengoc093/bizmatch-chat-bot-ai/',
    __FILE__,
    'bizmatch-chat-bot-ai'
);

// 3. Khai báo Branch chính
$bizmatch_update_checker->setBranch('main');

if (!defined('ABSPATH')) exit;

define('BIZMATCH_BOT_PATH', plugin_dir_path(__FILE__));
define('BIZMATCH_BOT_URL', plugin_dir_url(__FILE__));

require_once BIZMATCH_BOT_PATH . 'admin-settings.php';
require_once BIZMATCH_BOT_PATH . 'rest-api.php';

add_action('wp_enqueue_scripts', 'bizmatch_bot_enqueue_assets');
function bizmatch_bot_enqueue_assets() {
    $css_ver = filemtime(BIZMATCH_BOT_PATH . 'assets/css/style.css');
    $js_ver  = filemtime(BIZMATCH_BOT_PATH . 'assets/js/script.js');

    wp_enqueue_style('bizmatch-bot-css', BIZMATCH_BOT_URL . 'assets/css/style.css', array(), $css_ver);
    
    // Đọc màu thương hiệu từ Admin và Inject CSS trực tiếp
    $brand_color = get_option('bizmatch_brand_color', '#0068ff');
    $custom_css  = "
        :root {
            --bizmatch-brand-color: {$brand_color};
        }
        .bizmatch-launcher,
        .bizmatch-header,
        #bizmatch-chat-send {
            background-color: var(--bizmatch-brand-color) !important;
        }
    ";
    wp_add_inline_style('bizmatch-bot-css', $custom_css);

    wp_enqueue_script('bizmatch-bot-js', BIZMATCH_BOT_URL . 'assets/js/script.js', array(), $js_ver, true);

    wp_localize_script('bizmatch-bot-js', 'BizmatchBotData', array(
        'apiUrl'   => esc_url_raw(rest_url('bizmatch-bot/v1/chat')),
        'nonce'    => wp_create_nonce('wp_rest'),
        'botName'  => esc_html(get_option('bizmatch_bot_name', 'Bizmatch AI')),
        'position' => esc_attr(get_option('bizmatch_chat_position', 'right'))
    ));
}

add_action('wp_footer', 'bizmatch_bot_render_ui');
function bizmatch_bot_render_ui() {
    $bot_name = get_option('bizmatch_bot_name', 'Bizmatch AI');
    $robot_icon = '<svg class="bizmatch-bot-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12 2a1 1 0 0 1 1 1v1.05A7.002 7.002 0 0 1 19 11v1h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-1H3a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1v-1a7.002 7.002 0 0 1 6-6.95V3a1 1 0 0 1 1-1zm-4 8a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm8 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>';
    ?>
    <div id="bizmatch-chat-launcher" class="bizmatch-launcher">
        <?php echo $robot_icon; ?>
        <span><?php echo esc_html($bot_name); ?></span>
    </div>

    <div id="bizmatch-chat-box" class="bizmatch-box" style="display: none;">
        <div class="bizmatch-header">
            <div class="bizmatch-header-title">
                <?php echo $robot_icon; ?>
                <span><?php echo esc_html($bot_name); ?></span>
            </div>
            <span id="bizmatch-chat-close" style="cursor:pointer;">✕</span>
        </div>
        <div id="bizmatch-chat-messages" class="bizmatch-messages">
            <div class="b-msg bot">Xin chào! Tôi là <?php echo esc_html($bot_name); ?>. Tôi có thể giúp gì cho bạn?</div>
        </div>
        <div class="bizmatch-input-container">
            <input type="text" id="bizmatch-chat-input" placeholder="Nhập câu hỏi..." autocomplete="off" />
            <button id="bizmatch-chat-send" type="button">Gửi</button>
        </div>
    </div>
    <?php
}
