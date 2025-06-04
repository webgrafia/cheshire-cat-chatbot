<?php

namespace webgrafia\cheshirecat\inc\admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
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
            update_option('cheshire_chat_text_color', '#333333');
            update_option('cheshire_chat_user_message_color', '#4caf50');
            update_option('cheshire_chat_bot_message_color', '#ffffff');
            update_option('cheshire_chat_button_color', '#0078d7');
            update_option('cheshire_chat_header_color', '#ffffff');
            update_option('cheshire_chat_font_family', 'Arial, sans-serif');

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
            if (isset($_POST['cheshire_chat_header_color'])) {
                $cheshire_chat_header_color = sanitize_hex_color(wp_unslash($_POST['cheshire_chat_header_color']));
                update_option('cheshire_chat_header_color', $cheshire_chat_header_color);
            }
            if (isset($_POST['cheshire_chat_font_family'])) {
                $cheshire_chat_font_family = sanitize_text_field(wp_unslash($_POST['cheshire_chat_font_family']));
                update_option('cheshire_chat_font_family', $cheshire_chat_font_family);
            }
            if (isset($_POST['cheshire_chat_welcome_message'])) {
                $cheshire_chat_welcome_message = sanitize_textarea_field(wp_unslash($_POST['cheshire_chat_welcome_message']));
                update_option('cheshire_chat_welcome_message', $cheshire_chat_welcome_message);
            }
            if (isset($_POST['cheshire_plugin_input_placeholder'])) {
                $input_placeholder = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_input_placeholder']));
                update_option('cheshire_plugin_input_placeholder', $input_placeholder);
            }
        }
    }

    $cheshire_chat_background_color = get_option('cheshire_chat_background_color', '#ffffff');
    $cheshire_chat_text_color = get_option('cheshire_chat_text_color', '#333333');
    $cheshire_chat_user_message_color = get_option('cheshire_chat_user_message_color', '#4caf50');
    $cheshire_chat_bot_message_color = get_option('cheshire_chat_bot_message_color', '#ffffff');
    $cheshire_chat_button_color = get_option('cheshire_chat_button_color', '#0078d7');
    $cheshire_chat_header_color = get_option('cheshire_chat_header_color', '#ffffff');
    $cheshire_chat_font_family = get_option('cheshire_chat_font_family', 'Arial, sans-serif');
    $cheshire_chat_welcome_message = get_option('cheshire_chat_welcome_message', __('Hello! How can I help you?', 'cheshire-cat-chatbot'));
    $cheshire_chat_avatar_image = get_option('cheshire_chat_avatar_image', '');
    $cheshire_plugin_input_placeholder = get_option('cheshire_plugin_input_placeholder', __('Type your message...', 'cheshire-cat-chatbot'));
    ?>
    <div class="wrap">
        <h1><?php if (function_exists('get_admin_page_title')) {
                echo esc_html(get_admin_page_title());
            } ?></h1>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('cheshire_style_save_settings', 'cheshire_style_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Chat Header Color', 'cheshire-cat-chatbot'); ?></th>
                    <td>
                        <input type="color" name="cheshire_chat_header_color" value="<?php echo esc_attr($cheshire_chat_header_color); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Chat Background Color', 'cheshire-cat-chatbot'); ?></th>
                    <td><input type="color" name="cheshire_chat_background_color" value="<?php echo esc_attr($cheshire_chat_background_color); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Chat Text Color', 'cheshire-cat-chatbot'); ?></th>
                    <td><input type="color" name="cheshire_chat_text_color" value="<?php echo esc_attr($cheshire_chat_text_color); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Chat User Message Color', 'cheshire-cat-chatbot'); ?></th>
                    <td><input type="color" name="cheshire_chat_user_message_color" value="<?php echo esc_attr($cheshire_chat_user_message_color); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Chat Bot Message Color', 'cheshire-cat-chatbot'); ?></th>
                    <td><input type="color" name="cheshire_chat_bot_message_color" value="<?php echo esc_attr($cheshire_chat_bot_message_color); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Chat Button Color', 'cheshire-cat-chatbot'); ?></th>
                    <td><input type="color" name="cheshire_chat_button_color" value="<?php echo esc_attr($cheshire_chat_button_color); ?>" /></td>
                </tr>
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
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Chat Avatar Image', 'cheshire-cat-chatbot'); ?></th>
                    <td>
                        <?php if (!empty($cheshire_chat_avatar_image)) : ?>
                            <div style="margin-bottom: 10px;">
                                <img src="<?php echo esc_url($cheshire_chat_avatar_image); ?>" alt="Avatar" style="max-width: 100px; height: auto;" />
                                <button type="submit" name="remove_avatar" class="button button-secondary" style="margin-left: 10px;">
                                    <?php esc_html_e('Remove Avatar', 'cheshire-cat-chatbot'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="cheshire_chat_avatar_image" accept="image/*" />
                        <p class="description"><?php esc_html_e('Upload a custom avatar image. If none is provided, a default robot avatar will be used.', 'cheshire-cat-chatbot'); ?></p>
                        <p class="description"><strong><?php esc_html_e('Note:', 'cheshire-cat-chatbot'); ?></strong> <?php printf(
                            /* translators: %s: URL to the Configuration page */
                            esc_html__('Make sure to enable the avatar feature in the %s page for the avatar to be displayed.', 'cheshire-cat-chatbot'),
                            '<a href="' . esc_url(admin_url('admin.php?page=cheshire-cat-configuration')) . '">' . esc_html__('Configuration', 'cheshire-cat-chatbot') . '</a>'
                        ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
