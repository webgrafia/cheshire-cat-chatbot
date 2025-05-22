<?php
/*
Plugin Name: Cheshire Cat Chatbot
Description: A WordPress plugin to integrate the Cheshire Cat AI chatbot, offering seamless conversational AI for your site.
Version: 0.4
Author: Marco Buttarini
Author URI: https://bititup.it/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: cheshire-cat-chatbot
Domain Path: /languages
Requires at least: 5.8
Requires PHP: 7.4
Tested up to: 6.8
*/

namespace webgrafia\cheshirecat;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/inc/admin.php';
require_once __DIR__ . '/inc/shortcodes.php';
require_once __DIR__ . '/inc/ajax.php';
require_once __DIR__ . '/inc/classes/CustomCheshireCatClient.php';
require_once __DIR__ . '/inc/classes/CustomCheshireCat.php';

/**
 * Enqueue scripts and styles.
 */
function cheshirecat_enqueue_scripts()
{
    wp_enqueue_script('cheshire-chat-js', plugins_url('/assets/js/chat.js', __FILE__), array('jquery'), '1.0', true);
    wp_enqueue_style('cheshire-chat-css', plugins_url('/assets/css/chat.css', __FILE__), array(), '1.0');
    wp_localize_script('cheshire-chat-js', 'cheshire_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cheshire_ajax_nonce')
    ));

    wp_enqueue_style('font-awesome-css', plugins_url('/assets/css/font-awesome/all.min.css', __FILE__), array(), '1.0');

    // Add dynamic CSS
    wp_add_inline_style('cheshire-chat-css', cheshirecat_generate_dynamic_css());
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\cheshirecat_enqueue_scripts');


/**
 * Generate dynamic CSS based on the saved style settings.
 */
function cheshirecat_generate_dynamic_css()
{
    $chat_background_color = get_option('cheshire_chat_background_color', '#ffffff');
    $chat_text_color = get_option('cheshire_chat_text_color', '#333333');
    $chat_user_message_color = get_option('cheshire_chat_user_message_color', '#4caf50');
    $chat_bot_message_color = get_option('cheshire_chat_bot_message_color', '#ffffff');
    $chat_button_color = get_option('cheshire_chat_button_color', '#0078d7');
    $chat_font_family = get_option('cheshire_chat_font_family', 'Arial, sans-serif');

    $custom_css = "
        #cheshire-chat-container {
            background-color: " . esc_attr($chat_background_color) . ";
            font-family: " . esc_attr($chat_font_family) . ";
        }
        #cheshire-chat-messages {
            background-color: " . esc_attr($chat_background_color) . ";
        }
        .user-message {
            background-color: " . esc_attr($chat_user_message_color) . ";
        }
        .bot-message {
            background-color: " . esc_attr($chat_bot_message_color) . ";
        }
        #cheshire-chat-send {
            color: " . esc_attr($chat_button_color) . ";
        }
        #cheshire-chat-input {
            color: " . esc_attr($chat_text_color) . ";
        }
        .bot-message, .error-message {
            color: " . esc_attr($chat_text_color) . ";
        }
        .user-message {
            color: #fff;
        }
    ";

    return $custom_css;
}

/**
 * Display the welcome message.
 */
function cheshirecat_display_welcome_message()
{
    $welcome_message = get_option('cheshire_chat_welcome_message', __('Hello! How can I help you?', 'cheshire-cat-chatbot'));
    echo '<div class="bot-message"><p>' . esc_html($welcome_message) . '</p></div>';
}
