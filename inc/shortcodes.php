<?php

namespace webgrafia\cheshirecat;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Shortcode to display the chat
function cheshirecat_chat_shortcode()
{
    ob_start();

    ?>
    <div id="cheshire-chat-container">
        <div id="cheshire-chat-messages">
            <?php cheshirecat_display_welcome_message(); ?>
        </div>
        <div id="cheshire-chat-input-container">
            <input type="text" id="cheshire-chat-input" placeholder="<?php esc_attr_e('Type your message...', 'cheshire-cat-chatbot'); ?>">
            <button id="cheshire-chat-send"></button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('cheshire_chat', __NAMESPACE__ . '\cheshirecat_chat_shortcode');

// Add the chat to all pages if the option is enabled
function cheshirecat_add_global_chat()
{
    $cheshire_global_chat = get_option('cheshire_plugin_global_chat');
    if ($cheshire_global_chat === 'on') {
        echo do_shortcode('[cheshire_chat]');
    }
}
add_action('wp_footer', __NAMESPACE__ . '\cheshirecat_add_global_chat');
