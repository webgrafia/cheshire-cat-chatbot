<?php

namespace webgrafia\cheshirecat\inc\admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Playground page callback.
 * 
 * This page provides a full-page chat interface for administrators to test the Cheshire Cat chatbot.
 */
function cheshirecat_playground_page()
{
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'cheshire-cat-chatbot'));
    }

    // Check if avatar is enabled
    $avatar_enabled = get_option('cheshire_plugin_enable_avatar', 'off');
    $avatar_class = ($avatar_enabled === 'on') ? 'with-avatar' : '';
    $avatar_image = get_option('cheshire_chat_avatar_image', '');
    $default_avatar = CHESHIRE_CAT_PLUGIN_URL . 'assets/img/default-avatar.svg';

    // Scripts and styles are now enqueued via the admin_enqueue_scripts hook in the main plugin file
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <div class="playground-header">
            <p><?php esc_html_e('Welcome to the Cheshire Cat Chatbot Playground! This is a full-page chat interface where you can test the chatbot as an administrator.', 'cheshire-cat-chatbot'); ?></p>
            <p><?php esc_html_e('Use this playground to test your chatbot configuration and responses before making it available to your users.', 'cheshire-cat-chatbot'); ?></p>
        </div>

        <div id="cheshire-chat-container" class="<?php echo esc_attr($avatar_class . ' playground'); ?>">
            <div id="cheshire-chat-messages">
                <?php \webgrafia\cheshirecat\cheshirecat_display_welcome_message(); ?>
            </div>
            <div id="cheshire-chat-input-container">
                <input type="text" id="cheshire-chat-input" placeholder="<?php echo esc_attr(get_option('cheshire_plugin_input_placeholder', __('Type your message...', 'cheshire-cat-chatbot'))); ?>">
                <button id="cheshire-chat-send"></button>
            </div>
            <?php if ($avatar_enabled === 'on') : ?>
            <div id="cheshire-chat-avatar">
                <img src="<?php echo esc_url(!empty($avatar_image) ? $avatar_image : $default_avatar); ?>" alt="Chat Avatar">
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
