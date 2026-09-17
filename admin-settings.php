<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_options_page('Bizmatch Bot Settings', 'Bizmatch Chatbot', 'manage_options', 'bizmatch-bot-settings', 'bizmatch_bot_settings_page');
});

add_action('admin_init', function () {
    register_setting('bizmatch-bot-settings-group', 'bizmatch_api_key', 'sanitize_text_field');
    register_setting('bizmatch-bot-settings-group', 'bizmatch_api_key_2', 'sanitize_text_field');
    register_setting('bizmatch-bot-settings-group', 'bizmatch_bot_name', 'sanitize_text_field');
    register_setting('bizmatch-bot-settings-group', 'bizmatch_industry_scope', 'sanitize_text_field');
    register_setting('bizmatch-bot-settings-group', 'bizmatch_system_instruction', 'sanitize_textarea_field');
    register_setting('bizmatch-bot-settings-group', 'bizmatch_chat_position', 'sanitize_text_field');
    register_setting('bizmatch-bot-settings-group', 'bizmatch_brand_color', 'sanitize_hex_color');
});

function bizmatch_bot_settings_page() {
    if (!current_user_can('manage_options')) return;

    $position    = get_option('bizmatch_chat_position', 'right');
    $bot_name    = get_option('bizmatch_bot_name', 'Bizmatch AI');
    $industry    = get_option('bizmatch_industry_scope', 'Thiết kế website');
    $brand_color = get_option('bizmatch_brand_color', '#0068ff');
    
    $default_instruction = "Bạn là chuyên gia tư vấn duy nhất về lĩnh vực [LĨNH VỰC].

QUY TẮC BẮT BUỘC:
1. CHỈ trả lời câu hỏi liên quan đến [LĨNH VỰC].
2. Nếu câu hỏi ngoài lề, từ chối lịch sự và ngắn gọn.
3. Trả lời dưới 4 câu, lịch sự và xưng là [TÊN_BOT].";

    $instruction = get_option('bizmatch_system_instruction', $default_instruction);
    ?>
    <div class="wrap">
        <h2>Cấu hình Bizmatch Chat Bot AI</h2>

        <!-- Khung Hướng dẫn lấy API Key -->
        <div style="background: #fff; border-left: 4px solid #2271b1; padding: 15px 20px; margin: 15px 0 25px; box-shadow: 0 1px 3px rgba(0,0,0,.05); border-radius: 4px;">
            <h3 style="margin-top: 0; color: #1d2327; display: flex; align-items: center; gap: 8px;">
                💡 Hướng dẫn lấy Gemini API Key miễn phí
            </h3>
            <ol style="margin-bottom: 0; padding-left: 20px; line-height: 1.7; color: #50575e;">
                <li>Truy cập vào trang quản lý API: <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" style="font-weight: 600;">Google AI Studio API Keys ↗</a></li>
                <li>Đăng nhập bằng <strong>tài khoản Google</strong> của bạn.</li>
                <li>Nhấn vào nút <strong>"Create API key"</strong> (Tạo mã API mới).</li>
                <li>Chọn dự án (hoặc tạo dự án mặc định) rồi bấm <strong>Create API key in existing project</strong>.</li>
                <li>Sao chép (Copy) đoạn mã được tạo và dán vào ô <strong>Gemini API Key 1</strong> ở bên dưới.</li>
            </ol>
            <p style="margin: 10px 0 0; font-size: 13px; color: #646970;">
                📌 <i>Mẹo: Bạn nên tạo thêm 1 Key thứ hai từ một tài khoản Google khác dán vào ô <strong>API Key 2 (Dự phòng)</strong> để chatbot tự chuyển Key khi bị nghẽn (Lỗi 429).</i>
            </p>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('bizmatch-bot-settings-group'); ?>
            <?php do_settings_sections('bizmatch-bot-settings-group'); ?>
            <table class="form-table">
                <tr>
                    <th>Gemini API Key 1 (Chính):</th>
                    <td><input type="password" name="bizmatch_api_key" value="<?php echo esc_attr(get_option('bizmatch_api_key')); ?>" style="width: 500px;" required /></td>
                </tr>
                <tr>
                    <th>Gemini API Key 2 (Dự phòng):</th>
                    <td>
                        <input type="password" name="bizmatch_api_key_2" value="<?php echo esc_attr(get_option('bizmatch_api_key_2')); ?>" style="width: 500px;" />
                        <p class="description">Không bắt buộc. Dùng để tự động luân chuyển khi Key 1 bị giới hạn lượt gọi.</p>
                    </td>
                </tr>
                <tr>
                    <th>Tên Trợ lý AI:</th>
                    <td><input type="text" name="bizmatch_bot_name" value="<?php echo esc_attr($bot_name); ?>" style="width: 500px;" required /></td>
                </tr>
                <tr>
                    <th>Màu thương hiệu:</th>
                    <td>
                        <input type="color" id="bizmatch_color_picker" value="<?php echo esc_attr($brand_color); ?>" style="height: 38px; width: 50px; padding: 2px; vertical-align: middle; cursor: pointer;" oninput="document.getElementById('bizmatch_color_text').value = this.value;" />
                        <input type="text" id="bizmatch_color_text" name="bizmatch_brand_color" value="<?php echo esc_attr($brand_color); ?>" placeholder="#0068ff" style="width: 120px; vertical-align: middle;" oninput="if(/^#[0-9A-F]{6}$/i.test(this.value)) document.getElementById('bizmatch_color_picker').value = this.value;" />
                        <p class="description">Nhập/Dán trực tiếp mã Hex (ví dụ: <code>#0d3562</code>) hoặc chọn màu bằng ô bên trái.</p>
                    </td>
                </tr>
                <tr>
                    <th>Chuyên ngành hỗ trợ:</th>
                    <td><input type="text" name="bizmatch_industry_scope" value="<?php echo esc_attr($industry); ?>" style="width: 500px;" /></td>
                </tr>
                <tr>
                    <th>Vị trí hiển thị:</th>
                    <td>
                        <select name="bizmatch_chat_position">
                            <option value="right" <?php selected($position, 'right'); ?>>Góc Phải</option>
                            <option value="left" <?php selected($position, 'left'); ?>>Góc Trái</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Prompt System:</th>
                    <td><textarea name="bizmatch_system_instruction" rows="8" style="width: 500px;"><?php echo esc_textarea($instruction); ?></textarea></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}