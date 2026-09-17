<?php
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {
    register_rest_route('bizmatch-bot/v1', '/chat', array(
        'methods'             => 'POST',
        'callback'            => 'bizmatch_bot_handle_chat',
        'permission_callback' => '__return_true',
    ));
});

function bizmatch_bot_handle_chat($request) {
    $params = $request->get_json_params();
    $user_message = isset($params['message']) ? sanitize_text_field($params['message']) : '';

    if (empty($user_message)) {
        return new WP_REST_Response(array('reply' => 'Vui lòng nhập câu hỏi.'), 400);
    }

    $key_1       = trim(get_option('bizmatch_api_key', ''));
    $key_2       = trim(get_option('bizmatch_api_key_2', ''));
    $bot_name    = get_option('bizmatch_bot_name', 'Bizmatch AI');
    $industry    = get_option('bizmatch_industry_scope', 'Thiết kế website');
    $instruction = get_option('bizmatch_system_instruction', '');

    // Lọc lấy danh sách các Key có nhập dữ liệu
    $api_keys = array_filter(array($key_1, $key_2));

    if (empty($api_keys)) {
        return new WP_REST_Response(array('reply' => 'Lỗi: Chưa cấu hình API Key trong Admin.'), 200);
    }

    $instruction = str_replace('[LĨNH VỰC]', $industry, $instruction);
    $instruction = str_replace('[TÊN_BOT]', $bot_name, $instruction);

    // Bổ sung quy tắc chèn Link Markdown
    $instruction .= "\n\nQUY TẮC HIỂN THỊ LINK:\n- Khi gợi ý bài viết hoặc sản phẩm, BẮT BUỘC chèn đường dẫn dưới dạng Markdown: [Tên bài viết/Sản phẩm](URL).\n- Tuyệt đối không tự bịa ra URL không có trong dữ liệu được cung cấp.";

    // 1. Quét nội dung chi tiết bài viết/sản phẩm liên quan từ Website
    $site_context = bizmatch_bot_smart_search_content($user_message);

    // 2. Ghép ngữ cảnh tìm kiếm vào tin nhắn gửi sang AI
    $final_user_message = $user_message;
    if (!empty($site_context)) {
        $final_user_message .= "\n\n--- DỮ LIỆU THAM KHẢO TỪ WEBSITE ---\n" . $site_context;
    }

    $body = array(
        'systemInstruction' => array(
            'parts' => array(
                array('text' => $instruction)
            )
        ),
        'contents' => array(
            array(
                'role'  => 'user',
                'parts' => array(
                    array('text' => $final_user_message)
                )
            )
        )
    );

    $reply = '';
    $is_success = false;
    $last_err_msg = '';

    // Lặp qua các Key để dự phòng nếu Key trước bị lỗi / 429
    foreach ($api_keys as $current_key) {
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=' . $current_key;

        $response = wp_remote_post($endpoint, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body'    => json_encode($body),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            $last_err_msg = 'Lỗi kết nối Server: ' . $response->get_error_message();
            continue; // Lỗi mạng -> Thử Key tiếp theo
        }

        $status_code   = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        // Trường hợp gọi thành công
        if ($status_code === 200 && !empty($response_body['candidates'][0]['content']['parts'][0]['text'])) {
            $reply = trim($response_body['candidates'][0]['content']['parts'][0]['text']);
            $is_success = true;
            break; // Thoát vòng lặp ngay khi có kết quả
        }

        // Bắt thông báo lỗi của Key hiện tại để phòng trường hợp tất cả Key đều hỏng
        if ($status_code === 429) {
            $last_err_msg = 'Hệ thống đang bận do quá nhiều lượt truy cập cùng lúc. Bạn vui lòng đợi khoảng 1 phút rồi thử lại nhé!';
        } else {
            $last_err_msg = isset($response_body['error']['message']) ? $response_body['error']['message'] : 'Lỗi không xác định';
        }
    }

    if (!$is_success) {
        return new WP_REST_Response(array('reply' => $last_err_msg), 200);
    }

    return new WP_REST_Response(array('reply' => $reply), 200);
}

/**
 * Hàm tìm kiếm thông minh: Lấy NỘI DUNG CHI TIẾT bài viết/sản phẩm
 */
function bizmatch_bot_smart_search_content($keyword) {
    $post_types = array('post', 'page');
    if (post_type_exists('product')) {
        $post_types[] = 'product';
    }

    $args = array(
        's'              => $keyword,
        'post_type'      => $post_types,
        'post_status'    => 'publish',
        'posts_per_page' => 2,
    );

    $query = new WP_Query($args);
    $context = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id   = get_the_ID();
            $title     = get_the_title();
            $link      = get_permalink();
            $post_type = get_post_type();

            $raw_content   = get_the_content();
            $clean_content = strip_shortcodes($raw_content);
            $clean_content = wp_strip_all_tags($clean_content);
            $clean_content = preg_replace('/\s+/', ' ', $clean_content);

            $detailed_text = mb_substr(trim($clean_content), 0, 1200) . '...';

            if ($post_type === 'product' && class_exists('WooCommerce')) {
                $product = wc_get_product($post_id);
                $price   = wp_strip_all_tags($product->get_price_html());
                $context .= "- [SẢN PHẨM] Tên: {$title} | Giá: {$price} | Link: {$link}\n  Chi tiết: {$detailed_text}\n\n";
            } else {
                $context .= "- [BÀI VIẾT/TRANG] Tên: {$title} | Link: {$link}\n  Chi tiết: {$detailed_text}\n\n";
            }
        }
        wp_reset_postdata();
    }

    return $context;
}