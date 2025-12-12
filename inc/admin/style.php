<?php

namespace webgrafia\cheshirecat\inc\admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

function adjustColorBrightness($hex, $percent = 15) {
    // Rimuovi #
    $hex = ltrim($hex, '#');

    // Espandi formato corto
    if (strlen($hex) == 3) {
        $hex = $hex[0].$hex[0] . $hex[1].$hex[1] . $hex[2].$hex[2];
    }

    // RGB da HEX
    $r = hexdec(substr($hex, 0, 2)) / 255;
    $g = hexdec(substr($hex, 2, 2)) / 255;
    $b = hexdec(substr($hex, 4, 2)) / 255;

    // Calcola max e min
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $delta = $max - $min;

    // Luminosità
    $l = ($max + $min) / 2;

    // Saturazione
    if ($delta == 0) {
        $h = $s = 0; // Colore grigio
    } else {
        $s = $delta / (1 - abs(2 * $l - 1));

        switch ($max) {
            case $r:
                $h = 60 * fmod((($g - $b) / $delta), 6);
                break;
            case $g:
                $h = 60 * ((($b - $r) / $delta) + 2);
                break;
            case $b:
                $h = 60 * ((($r - $g) / $delta) + 4);
                break;
        }
    }

    // Normalizza hue
    if ($h < 0) $h += 360;

    // Decidi se schiarire o scurire
    if ($l >= 0.5) {
        $l = max(0, $l - $percent / 100); // scurisce
    } else {
        $l = min(1, $l + $percent / 100); // schiarisce
    }

    // Converti HSL -> RGB
    $c = (1 - abs(2 * $l - 1)) * $s;
    $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
    $m = $l - $c / 2;

    if ($h < 60) {
        $r1 = $c; $g1 = $x; $b1 = 0;
    } elseif ($h < 120) {
        $r1 = $x; $g1 = $c; $b1 = 0;
    } elseif ($h < 180) {
        $r1 = 0; $g1 = $c; $b1 = $x;
    } elseif ($h < 240) {
        $r1 = 0; $g1 = $x; $b1 = $c;
    } elseif ($h < 300) {
        $r1 = $x; $g1 = 0; $b1 = $c;
    } else {
        $r1 = $c; $g1 = 0; $b1 = $x;
    }

    // RGB finale
    $rFinal = round(($r1 + $m) * 255);
    $gFinal = round(($g1 + $m) * 255);
    $bFinal = round(($b1 + $m) * 255);

    return sprintf("#%02x%02x%02x", $rFinal, $gFinal, $bFinal);
}


/**
 * Style page callback.
 */
function cheshirecat_style_page()
{
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'cheshire-cat-chatbot'));
    }

    // Handle form submission
    if (isset($_POST['cheshire_style_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cheshire_style_nonce'])), 'cheshire_style_save_settings')) {
        // Check if reset colors button was clicked
        if (isset($_POST['reset_colors'])) {
            // Reset color options to default values
            update_option('cheshire_chat_background_color', '#ffffff');
            update_option('cheshire_chat_user_text_color', '#ffffff');
            update_option('cheshire_chat_bot_text_color', '#333333');
            update_option('cheshire_chat_user_message_color', '#4caf50');
            update_option('cheshire_chat_bot_message_color', '#ffffff');
            update_option('cheshire_chat_header_color', '#ffffff');
            update_option('cheshire_chat_header_buttons_color', '#999999');
            update_option('cheshire_chat_header_buttons_color_hover', '#666666');
            update_option('cheshire_chat_header_buttons_color_hover_background', '#f2f2f2');
            update_option('cheshire_chat_header_buttons_color_focus', '#0078d7');
            update_option('cheshire_chat_font_family', 'Arial, sans-serif');
            update_option('cheshire_chat_footer_color', '#ffffff');
            update_option('cheshire_chat_button_color', '#0078d7');
            update_option('cheshire_chat_button_color_hover', '#005bb5');
            update_option('cheshire_chat_button_color_hover_background', '#f2f2f2');
            update_option('cheshire_chat_button_color_focus', '#b3d7f3');
            update_option('cheshire_chat_button_color_active', '#004494');
            update_option('cheshire_chat_input_color', '#ffffff');
            update_option('cheshire_chat_input_text_color', '#2c3338');
            update_option('cheshire_chat_error_msg_bg', '#ffcccc');
            update_option('cheshire_chat_error_msg_border', '#ffaaaa');
            update_option('cheshire_chat_error_msg_color', '#991111');
            update_option('cheshire_chat_border_color', '#dddddd');

            // Add admin notice
            add_settings_error(
                'cheshire_cat_style_options',
                'cheshire_cat_colors_reset',
                __('Color settings have been reset to default values.', 'cheshire-cat-chatbot'),
                'success'
            );
        } 
        // Check if remove avatar button was clicked
        elseif (isset($_POST['remove_avatar'])) {
            // Remove the avatar image by setting the option to empty
            update_option('cheshire_chat_avatar_image', '');

            // Add admin notice
            add_settings_error(
                'cheshire_cat_style_options',
                'cheshire_cat_avatar_removed',
                __('Avatar image has been removed.', 'cheshire-cat-chatbot'),
                'success'
            );
        } else {
            // Handle avatar image upload
            if (!empty($_FILES['cheshire_chat_avatar_image']['name'])) {
                if (!function_exists('wp_handle_upload')) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }

                $uploadedfile = $_FILES['cheshire_chat_avatar_image'];
                $upload_overrides = array('test_form' => false);

                $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

                if ($movefile && !isset($movefile['error'])) {
                    update_option('cheshire_chat_avatar_image', $movefile['url']);
                }
            }

            if (isset($_POST['cheshire_chat_background_color'])) {
                $cheshire_chat_background_color = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_background_color']));
                update_option('cheshire_chat_background_color', $cheshire_chat_background_color);
            }
            if (isset($_POST['cheshire_chat_text_color'])) {
                $cheshire_chat_text_color = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_text_color']));
                update_option('cheshire_chat_text_color', $cheshire_chat_text_color);
            }
            if (isset($_POST['cheshire_chat_user_text_color'])) {
                $cheshire_chat_user_text_color = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_user_text_color']));
                update_option('cheshire_chat_user_text_color', $cheshire_chat_user_text_color);
            }
            if (isset($_POST['cheshire_chat_user_message_color'])) {
                $cheshire_chat_user_message_color = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_user_message_color']));
                update_option('cheshire_chat_user_message_color', $cheshire_chat_user_message_color);
            }
            if (isset($_POST['cheshire_chat_bot_message_color'])) {
                $cheshire_chat_bot_message_color = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_bot_message_color']));
                update_option('cheshire_chat_bot_message_color', $cheshire_chat_bot_message_color);
            }
            if (isset($_POST['cheshire_chat_button_color'])) {
                $cheshire_chat_button_color = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_button_color']));
                update_option('cheshire_chat_button_color', $cheshire_chat_button_color);
            }
            if (isset($_POST['cheshire_chat_button_color_hover'])) {
                $cheshire_chat_button_color_hover = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_button_color_hover']));
                update_option('cheshire_chat_button_color_hover', $cheshire_chat_button_color_hover);
            }
            if (isset($_POST['cheshire_chat_button_color_hover_background'])) {
                $cheshire_chat_button_color_hover_background = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_button_color_hover_background']));
                update_option('cheshire_chat_button_color_hover_background', $cheshire_chat_button_color_hover_background);
            }
            if (isset($_POST['cheshire_chat_button_color_focus'])) {
                $cheshire_chat_button_color_focus = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_button_color_focus']));
                update_option('cheshire_chat_button_color_focus', $cheshire_chat_button_color_focus);
            }
            if (isset($_POST['cheshire_chat_button_color_active'])) {
                $cheshire_chat_button_color_active = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_button_color_active']));
                update_option('cheshire_chat_button_color_active', $cheshire_chat_button_color_active);
            }
            if (isset($_POST['cheshire_chat_header_color'])) {
                $cheshire_chat_header_color = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_header_color']));
                update_option('cheshire_chat_header_color', $cheshire_chat_header_color);
            }
            if (isset($_POST['cheshire_chat_footer_color'])) {
                $cheshire_chat_footer_color = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_footer_color']));
                update_option('cheshire_chat_footer_color', $cheshire_chat_footer_color);
            }
            if (isset($_POST['cheshire_chat_font_family'])) {
                $cheshire_chat_font_family = sanitize_text_field(wp_unslash($_POST['cheshire_chat_font_family']));
                update_option('cheshire_chat_font_family', $cheshire_chat_font_family);
            }
            if (isset($_POST['cheshire_chat_header_buttons_color'])) {
                $cheshire_chat_header_buttons_color = sanitize_text_field(wp_unslash($_POST['cheshire_chat_header_buttons_color']));
                update_option('cheshire_chat_header_buttons_color', $cheshire_chat_header_buttons_color);
            }
            if (isset($_POST['cheshire_chat_header_buttons_color_hover'])) {
                $cheshire_chat_header_buttons_color_hover = sanitize_text_field(wp_unslash($_POST['cheshire_chat_header_buttons_color_hover']));
                update_option('cheshire_chat_header_buttons_color_hover', $cheshire_chat_header_buttons_color_hover);
            }
            if (isset($_POST['cheshire_chat_header_buttons_color_hover_background'])) {
                $cheshire_chat_header_buttons_color_hover_background = sanitize_text_field(wp_unslash($_POST['cheshire_chat_header_buttons_color_hover_background']));
                update_option('cheshire_chat_header_buttons_color_hover_background', $cheshire_chat_header_buttons_color_hover_background);
            }
            if (isset($_POST['cheshire_chat_header_buttons_color_focus'])) {
                $cheshire_chat_header_buttons_color_focus = sanitize_text_field(wp_unslash($_POST['cheshire_chat_header_buttons_color_focus']));
                update_option('cheshire_chat_header_buttons_color_focus', $cheshire_chat_header_buttons_color_focus);
            }
            if (isset($_POST['cheshire_chat_input_color'])) {
                $cheshire_chat_input_color = sanitize_text_field(wp_unslash($_POST['cheshire_chat_input_color']));
                update_option('cheshire_chat_input_color', $cheshire_chat_input_color);
            }
            if (isset($_POST['cheshire_chat_input_text_color'])) {
                $cheshire_chat_input_text_color = sanitize_text_field(wp_unslash($_POST['cheshire_chat_input_text_color']));
                update_option('cheshire_chat_input_text_color', $cheshire_chat_input_text_color);
            }
            if (isset($_POST['cheshire_chat_error_msg_bg'])) {
                $cheshire_chat_error_msg_bg = sanitize_text_field(wp_unslash($_POST['cheshire_chat_error_msg_bg']));
                update_option('cheshire_chat_error_msg_bg', $cheshire_chat_error_msg_bg);
            }
            if (isset($_POST['cheshire_chat_error_msg_border'])) {
                $cheshire_chat_error_msg_border = sanitize_text_field(wp_unslash($_POST['cheshire_chat_error_msg_border']));
                update_option('cheshire_chat_error_msg_border', $cheshire_chat_error_msg_border);
            }
            if (isset($_POST['cheshire_chat_error_msg_color'])) {
                $cheshire_chat_error_msg_color = sanitize_text_field(wp_unslash($_POST['cheshire_chat_error_msg_color']));
                update_option('cheshire_chat_error_msg_color', $cheshire_chat_error_msg_color);
            }
            if (isset($_POST['cheshire_chat_border_color'])) {
                $cheshire_chat_border_color = sanitize_text_field(wp_unslash($_POST['cheshire_chat_border_color']));
                update_option('cheshire_chat_border_color', $cheshire_chat_border_color);
            }
            if (isset($_POST['cheshire_chat_bot_text_color'])) {
                $cheshire_chat_bot_text_color = sanitize_text_field(wp_unslash($_POST['cheshire_chat_bot_text_color']));
                update_option('cheshire_chat_bot_text_color', $cheshire_chat_bot_text_color);
            }


            if (isset($_POST['cheshire_chat_welcome_message'])) {
                $cheshire_chat_welcome_message = sanitize_textarea_field(wp_unslash($_POST['cheshire_chat_welcome_message']));
                update_option('cheshire_chat_welcome_message', $cheshire_chat_welcome_message);
            }
            if (isset($_POST['cheshire_plugin_input_placeholder'])) {
                $input_placeholder = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_input_placeholder']));
                update_option('cheshire_plugin_input_placeholder', $input_placeholder);
            }

            // Save selected theme file (from assets/themes)
            if (isset($_POST['cheshire_chat_selected_theme'])) {
                $selected_theme_file = sanitize_file_name(wp_unslash($_POST['cheshire_chat_selected_theme']));
                $themes_dir = trailingslashit(dirname(__FILE__, 3)) . 'assets/themes/';
                // Allow empty (no selection) or an existing file in the themes directory
                if ($selected_theme_file === '' || file_exists($themes_dir . $selected_theme_file)) {
                    update_option('cheshire_chat_selected_theme', $selected_theme_file);
                }
            }
            // Avatar is always enabled, no need to check for the option
        }
    }

    $cheshire_chat_background_color = get_option('cheshire_chat_background_color', '#ffffff');
    $cheshire_chat_text_color = get_option('cheshire_chat_text_color', '#ffffff');
    $cheshire_chat_user_text_color = get_option('cheshire_chat_user_text_color', '#ffffff');
    $cheshire_chat_user_message_color = get_option('cheshire_chat_user_message_color', '#4caf50');
    $cheshire_chat_bot_text_color = get_option('cheshire_chat_bot_text_color', '#333333');
    $cheshire_chat_bot_message_color = get_option('cheshire_chat_bot_message_color', '#ffffff');
    $cheshire_chat_header_color = get_option('cheshire_chat_header_color', '#ffffff');
    $cheshire_chat_footer_color = get_option('cheshire_chat_footer_color', '#ffffff');
    $cheshire_chat_font_family = get_option('cheshire_chat_font_family', 'Arial, sans-serif');
    $cheshire_chat_welcome_message = get_option('cheshire_chat_welcome_message', __('Hello! How can I help you?', 'cheshire-cat-chatbot'));
    $cheshire_chat_avatar_image = get_option('cheshire_chat_avatar_image', '');
    $cheshire_plugin_input_placeholder = get_option('cheshire_plugin_input_placeholder', __('Type your message...', 'cheshire-cat-chatbot'));
    $cheshire_chat_header_buttons_color = get_option('cheshire_chat_header_buttons_color', '#999999');
    $cheshire_chat_header_buttons_color_hover = get_option('cheshire_chat_header_buttons_color_hover', '#666666');
    $cheshire_chat_header_buttons_color_hover_background = get_option('cheshire_chat_header_buttons_color_hover_background', '#f2f2f2');
    $cheshire_chat_header_buttons_color_focus = get_option('cheshire_chat_header_buttons_color_focus', '#0078d7');
    $cheshire_chat_button_color = get_option('cheshire_chat_button_color', '#0078d7');
    $cheshire_chat_button_color_hover = get_option('cheshire_chat_button_color_hover', '#005bb5');
    $cheshire_chat_button_color_hover_background = get_option('cheshire_chat_button_color_hover_background', '#f2f2f2');
    $cheshire_chat_button_color_focus = get_option('cheshire_chat_button_color_focus', '#b3d7f3');
    $cheshire_chat_button_color_active = get_option('cheshire_chat_button_color_active', '#004494');
    $cheshire_chat_input_color = get_option('cheshire_chat_input_color', '#ffffff');
    $cheshire_chat_input_text_color = get_option('cheshire_chat_input_text_color', '#2c3338');
    $cheshire_chat_error_msg_bg= get_option('cheshire_chat_error_msg_bg', '#ffcccc');
    $cheshire_chat_error_msg_border = get_option('cheshire_chat_error_msg_border', '#ffaaaa');
    $cheshire_chat_error_msg_color = get_option('cheshire_chat_error_msg_color', '#991111');
    $cheshire_chat_border_color = get_option('cheshire_chat_border_color', '#ddddd');
    // Avatar is always enabled
    $cheshire_plugin_enable_avatar = 'on';

    // Load selected theme file name
    $cheshire_selected_theme = get_option('cheshire_chat_selected_theme', '');

    // Build themes list from assets/themes
    $cheshire_available_themes = array();
    $cheshire_themes_dir = trailingslashit(dirname(__FILE__, 3)) . 'assets/themes/';
    if (is_dir($cheshire_themes_dir)) {
        $theme_files = glob($cheshire_themes_dir . '*.json');
        if ($theme_files) {
            foreach ($theme_files as $theme_path) {
                $basename = basename($theme_path);
                $label = $basename;
                $json = @file_get_contents($theme_path);
                if ($json !== false) {
                    $data = json_decode($json, true);
                    if (is_array($data) && !empty($data['chat-theme-name'])) {
                        $label = $data['chat-theme-name'];
                    } else {
                        // Fallback to filename without extension as name
                        $label = pathinfo($basename, PATHINFO_FILENAME) . ' (' . $basename . ')';
                    }
                }
                $cheshire_available_themes[] = array(
                    'file' => $basename,
                    'label' => $label,
                );
            }
        }
    }

    // Public URL for themes directory for use in JS (e.g., fetching JSON)
    $cheshire_themes_url = plugins_url('assets/themes/', dirname(__FILE__, 3) . '/cheshire-cat-chatbot.php');
    ?>
    <style type="text/css">
        :root {
            --chat-primary-color:  <?php echo esc_attr( $cheshire_chat_button_color  ); ?>;
            --chat-primary-hover:  <?php echo esc_attr( $cheshire_chat_bot_message_color  ); ?>;
            --chat-primary-active:  <?php echo esc_attr( $cheshire_chat_user_message_color  ); ?>;
            --chat-user-msg-bg: <?php echo esc_attr( $cheshire_chat_user_message_color  ); ?>;
            --chat-user-msg-color: <?php echo esc_attr( $cheshire_chat_user_text_color  ); ?>;
            --chat-bot-msg-bg: <?php echo esc_attr( $cheshire_chat_bot_message_color  ); ?>;
            --chat-bot-msg-color: <?php echo esc_attr( $cheshire_chat_bot_text_color  ); ?>;
            --chat-error-msg-bg: <?php echo esc_attr( $cheshire_chat_error_msg_bg ); ?>;
            --chat-error-msg-border: <?php echo esc_attr( $cheshire_chat_error_msg_border ); ?>;
            --chat-error-msg-color: <?php echo esc_attr( $cheshire_chat_error_msg_color ); ?>;
            --chat-border-color: <?php echo esc_attr( $cheshire_chat_border_color ); ?>;
            --chat-header-bg-color: <?php echo esc_attr($cheshire_chat_header_color); ?>;
            --chat-bg-color: <?php echo esc_attr($cheshire_chat_footer_color); ?>;
            --chat-footer-bg-color: <?php echo esc_attr($cheshire_chat_footer_color); ?>;
            --chat-messages-bg: <?php echo esc_attr( $cheshire_chat_background_color ); ?>;
            --chat-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --chat-input-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
            --chat-input-focus-shadow: inset 0 2px 6px rgba(0, 120, 215, 0.2);
            --chat-header-buttons-color: <?php echo esc_attr( $cheshire_chat_header_buttons_color ); ?>;
            --chat-header-buttons-color-hover: <?php echo esc_attr( $cheshire_chat_header_buttons_color_hover ); ?>;
            --chat-header-buttons-color-hover-background: <?php echo esc_attr( $cheshire_chat_header_buttons_color_hover_background ); ?>;
            --chat-header-buttons-color-focus: <?php echo esc_attr( $cheshire_chat_header_buttons_color_focus ); ?>;
            --chat-input-color: <?php echo esc_attr( $cheshire_chat_input_color ); ?>;
            --chat-input-text-color: <?php echo esc_attr( $cheshire_chat_input_text_color ); ?>;
            --chat-button-color-hover: <?php echo esc_attr( $cheshire_chat_button_color_hover ); ?>;
            --chat-button-color-hover-background: <?php echo esc_attr( $cheshire_chat_button_color_hover_background ); ?>;
            --chat-button-color-focus: <?php echo esc_attr( $cheshire_chat_button_color_focus ); ?>;
            --chat-button-color-active: <?php echo esc_attr( $cheshire_chat_button_color_active ); ?>;
        }

        #cheshire-chat-messages .user-message p {
            color: var(--chat-user-msg-color);
        }

        #cheshire-chat-messages .bot-message p {
            color: var(--chat-bot-msg-color);
        }

        #cheshire-chat-close, #cheshire-chat-new {
            color: var(--chat-header-buttons-color);
        }

        #cheshire-chat-close:hover,
        #cheshire-chat-new:hover {
            background-color: var(--chat-header-buttons-color-hover-background);
            color: var(--chat-header-buttons-color-hover);
        }

        #cheshire-chat-close:focus,
        #cheshire-chat-new:focus {
            outline: 2px solid var(--chat-header-buttons-color-focus);
            outline-offset: 2px;
        }

        #cheshire-chat-input {
            background-color: var(--chat-input-color);
            color: var(--chat-input-text-color);
        }

        #cheshire-chat-input::placeholder {
            color: var(--chat-input-text-color);
        }

        #cheshire-chat-send {
            color: var(--chat-primary-color);
        }

        #cheshire-chat-send:hover {
            color: var(--chat-button-color-hover);
            background-color: var(--chat-button-color-hover-background);
        }

        #cheshire-chat-send:active {
            color: var(--chat-button-color-active);
        }

        #cheshire-chat-send:focus {
            box-shadow: 0 0 0 2px var(--chat-button-color-focus);
        }

        /* ----------------------------------------
           10. Accessibility Improvements
        ---------------------------------------- */
        #cheshire-chat-input:focus,
        #cheshire-chat-send:focus,
        #cheshire-chat-close:focus { /* Added close button */
            outline: 2px solid var(--chat-button-color);
            outline-offset: 2px;
        }

        #cheshire-chat-messages .error-message p {
            color: var(--chat-error-msg-color);
            font-weight: 600;
        }
    </style>
    <div class="wrap cheshire-admin">
        <h1><?php if (function_exists('get_admin_page_title')) {
                echo esc_html(get_admin_page_title());
            } ?></h1>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('cheshire_style_save_settings', 'cheshire_style_nonce'); ?>
            <div class="cheshire-section">
                <h2><?php _e('Chat Appearance', 'cheshire-cat-chatbot'); ?></h2>

                <table class="form-table">
                    <tr>
                        <td style="width: 60%" class="mobile-width">
                            <div class="chechire-plugin-block">
                                <h3><?php esc_html_e('Main', 'cheshire-cat-chatbot'); ?></h3>
                                <table class="form-table">
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Theme Selected', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <select id="cheshire_chat_selected_theme" name="cheshire_chat_selected_theme" data-themes-base-url="<?php echo esc_attr($cheshire_themes_url); ?>">
                                                <option value=""><?php esc_html_e('No theme', 'cheshire-cat-chatbot'); ?></option>
                                                <?php if (!empty($cheshire_available_themes)) : ?>
                                                    <?php foreach ($cheshire_available_themes as $theme) : ?>
                                                        <option value="<?php echo esc_attr($theme['file']); ?>" <?php selected($cheshire_selected_theme, $theme['file']); ?>>
                                                            <?php echo esc_html($theme['label']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Border Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_border_color" value="<?php echo esc_attr($cheshire_chat_border_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-error">
                                        <th colspan="2">
                                            <button type="button" class="cheshire-accordion-toggle">
                                                <span class="dashicons dashicons-plus"></span>
                                                <?php esc_html_e('Messaggio di errore', 'cheshire-cat-chatbot'); ?>
                                                <div class="cheshire-accordion-swatch">
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_header_buttons_color); ?>;"></span>
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_header_buttons_color_hover); ?>;"></span>
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_header_buttons_color_hover_background); ?>;"></span>
                                                </div>
                                            </button>
                                        </th>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Header Error Message Background', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_error_msg_bg" value="<?php echo esc_attr($cheshire_chat_error_msg_bg); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Header Error Message Border', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_error_msg_border" value="<?php echo esc_attr($cheshire_chat_error_msg_border); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Header Error Message Text Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_error_msg_color" value="<?php echo esc_attr($cheshire_chat_error_msg_color); ?>" />
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="chechire-plugin-block">
                                <h3><?php esc_html_e('Header', 'cheshire-cat-chatbot'); ?></h3>
                                <table class="form-table">
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Header Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_header_color" value="<?php echo esc_attr($cheshire_chat_header_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-header">
                                        <th colspan="2">
                                            <button type="button" class="cheshire-accordion-toggle">
                                                <span class="dashicons dashicons-plus"></span>
                                                <?php esc_html_e('Chat Header Button', 'cheshire-cat-chatbot'); ?>
                                                <div class="cheshire-accordion-swatch">
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_header_buttons_color); ?>;"></span>
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_header_buttons_color_hover); ?>;"></span>
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_header_buttons_color_hover_background); ?>;"></span>
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_header_buttons_color_focus); ?>;"></span>
                                                </div>
                                            </button>
                                        </th>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Header Buttons Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_header_buttons_color" value="<?php echo esc_attr($cheshire_chat_header_buttons_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Header Buttons Color Hover', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_header_buttons_color_hover" value="<?php echo esc_attr($cheshire_chat_header_buttons_color_hover); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Header Buttons Color Hover Background', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_header_buttons_color_hover_background" value="<?php echo esc_attr($cheshire_chat_header_buttons_color_hover_background); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Header Buttons Color Focus', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_header_buttons_color_focus" value="<?php echo esc_attr($cheshire_chat_header_buttons_color_focus); ?>" />
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Content section -->
                            <div class="chechire-plugin-block">
                                <h3><?php esc_html_e('Content', 'cheshire-cat-chatbot'); ?></h3>
                                <table class="form-table">
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Background Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_background_color" value="<?php echo esc_attr($cheshire_chat_background_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat User Text Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_user_text_color" value="<?php echo esc_attr($cheshire_chat_user_text_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat User Message Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_user_message_color" value="<?php echo esc_attr($cheshire_chat_user_message_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Bot Text Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_bot_text_color" value="<?php echo esc_attr($cheshire_chat_bot_text_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Bot Message Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_bot_message_color" value="<?php echo esc_attr($cheshire_chat_bot_message_color); ?>" />
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Footer section -->
                            <div class="chechire-plugin-block">
                                <h3><?php esc_html_e('Footer', 'cheshire-cat-chatbot'); ?></h3>
                                <table class="form-table">
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Footer color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_footer_color" value="<?php echo esc_attr($cheshire_chat_footer_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-header">
                                        <th colspan="2">
                                            <button type="button" class="cheshire-accordion-toggle">
                                                <span class="dashicons dashicons-plus"></span>
                                                <?php esc_html_e('Chat Button Color', 'cheshire-cat-chatbot'); ?>
                                                <div class="cheshire-accordion-swatch">
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_button_color); ?>;"></span>
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_button_color_hover); ?>;"></span>
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_button_color_hover_background); ?>;"></span>
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_button_color_focus); ?>;"></span>
                                                    <span style="background: <?php echo esc_attr($cheshire_chat_button_color_active); ?>;"></span>
                                                </div>
                                            </button>
                                        </th>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Button Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_button_color" value="<?php echo esc_attr($cheshire_chat_button_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Button Color Hover', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_button_color_hover" value="<?php echo esc_attr($cheshire_chat_button_color_hover); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Button Color Hover Backround', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_button_color_hover_background" value="<?php echo esc_attr($cheshire_chat_button_color_hover_background); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Button Color Focus', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_button_color_focus" value="<?php echo esc_attr($cheshire_chat_button_color_focus); ?>" />
                                        </td>
                                    </tr>
                                    <tr class="cheshire-accordion-content">
                                        <th scope="row"><?php esc_html_e('Chat Button Color Active', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_button_color_active" value="<?php echo esc_attr($cheshire_chat_button_color_active); ?>" />
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Input Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_input_color" value="<?php echo esc_attr($cheshire_chat_input_color); ?>" />
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Input Text Color', 'cheshire-cat-chatbot'); ?></th>
                                        <td class="text-right">
                                            <input type="color" name="cheshire_chat_input_text_color" value="<?php echo esc_attr($cheshire_chat_input_text_color); ?>" />
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                        <td class="hide-mobile td-chat-container">
                            <div class="chat-modal">
                                <div id="cheshire-chat-container" class="with-avatar cheshire-chat-open">
                                    <div id="cheshire-chat-header">
                                        <button id="cheshire-chat-new" aria-label="New conversation" title="Start a new conversation" onclick="return false;">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button id="cheshire-chat-close" aria-label="Close chat" onclick="return false;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div id="cheshire-chat-messages">
                                        <div class="bot-message">
                                            <p><?php echo get_option('cheshire_chat_welcome_message'); ?></p>
                                        </div>
                                        <div class="user-message">
                                            <p>Just a test—like a little game in Wonderland. How may I help you?</p>
                                        </div>
                                    </div>
                                    <div id="cheshire-predefined-responses">
                                        <span class="predefined-response-tag">White Rabbit</span>
                                        <span class="predefined-response-tag">Drink Me</span>
                                    </div>
                                    <div id="cheshire-chat-input-container">
                                        <input type="text" id="cheshire-chat-input" placeholder="<?php echo esc_attr(get_option('cheshire_plugin_input_placeholder')); ?>">
                                        <button id="cheshire-chat-send" onclick="return false;"><i class="far fa-arrow-alt-circle-right"></i></button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="chechire-plugin-block" style="margin-top: 10px;">
                                <h3>Testi e font</h3>
                                <table class="form-table">
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Font Family', 'cheshire-cat-chatbot'); ?></th>
                                        <td><input type="text" name="cheshire_chat_font_family" value="<?php echo esc_attr($cheshire_chat_font_family); ?>" /></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Welcome Message', 'cheshire-cat-chatbot'); ?></th>
                                        <td><textarea name="cheshire_chat_welcome_message" rows="5" cols="50"><?php echo esc_textarea($cheshire_chat_welcome_message); ?></textarea></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><?php esc_html_e('Chat Input Placeholder', 'cheshire-cat-chatbot'); ?></th>
                                        <td>
                                            <input type="text" name="cheshire_plugin_input_placeholder" value="<?php echo esc_attr($cheshire_plugin_input_placeholder); ?>" class="regular-text" />
                                            <p class="description"><?php esc_html_e('Customize the placeholder text shown in the chat input field.', 'cheshire-cat-chatbot'); ?></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div><!-- End of Chat Appearance section -->

            <div class="cheshire-section">
                <h2><?php _e('Avatar Settings', 'cheshire-cat-chatbot'); ?></h2>
                <p class="description"><?php esc_html_e('Customize the avatar that appears in the chat interface.', 'cheshire-cat-chatbot'); ?></p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Chat Avatar Image', 'cheshire-cat-chatbot'); ?></th>
                        <td>
                            <?php if (!empty($cheshire_chat_avatar_image)) : ?>
                                <div class="avatar-preview">
                                    <img src="<?php echo esc_url($cheshire_chat_avatar_image); ?>" alt="Avatar" />
                                    <button type="submit" name="remove_avatar" class="button button-secondary remove-avatar-button">
                                        <?php esc_html_e('Remove Avatar', 'cheshire-cat-chatbot'); ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="cheshire_chat_avatar_image" accept="image/*" />
                            <p class="description"><?php esc_html_e('Upload a custom avatar image. If none is provided, a default robot avatar will be used.', 'cheshire-cat-chatbot'); ?></p>
                        </td>
                    </tr>
                </table>
            </div><!-- End of Avatar Settings section -->

            <?php submit_button(__( 'Save Changes' ), 'primary large', 'save_changes', false ); ?>
            <?php submit_button(__( 'Reset Colors' ), 'secondary large button-warning', 'reset_colors', false ); ?>
        </form>
    </div>
    <?php
}

/*




 */