<?php
/**
 * Cheshire Cat Chatbot
 *
 * @package           CheshireCatChatbot
 * @author            Marco Buttarini
 * @copyright         2023 Marco Buttarini
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Cheshire Cat Chatbot
 * Plugin URI:        https://cheshirecat.ai/
 * Description:       A WordPress plugin to integrate the Cheshire Cat AI chatbot, offering seamless conversational AI for your site.
 * Version:           0.4.1
 * Author:            Marco Buttarini
 * Author URI:        https://bititup.it/
 * License:           GPL-3.0-or-later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       cheshire-cat-chatbot
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Tested up to:      6.8
 */

namespace webgrafia\cheshirecat;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'CHESHIRE_CAT_VERSION', '0.4.1' );
define( 'CHESHIRE_CAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CHESHIRE_CAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load required files.
require_once CHESHIRE_CAT_PLUGIN_DIR . 'vendor/autoload.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/admin.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/shortcodes.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/ajax.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/classes/CustomCheshireCatClient.php'; // Load for backward compatibility
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/classes/CustomCheshireCat.php'; // Load for backward compatibility

/**
 * Enqueue scripts and styles for the frontend.
 *
 * @since 0.1
 * @return void
 */
function cheshirecat_enqueue_scripts() {
    $version = CHESHIRE_CAT_VERSION;

    // Enqueue main chat script.
    wp_enqueue_script(
        'cheshire-chat-js', 
        CHESHIRE_CAT_PLUGIN_URL . 'assets/js/chat.js', 
        array( 'jquery' ), 
        $version, 
        true
    );

    // Enqueue main chat styles.
    wp_enqueue_style(
        'cheshire-chat-css', 
        CHESHIRE_CAT_PLUGIN_URL . 'assets/css/chat.css', 
        array(), 
        $version
    );

    // Localize script with AJAX data.
    wp_localize_script(
        'cheshire-chat-js', 
        'cheshire_ajax_object', 
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cheshire_ajax_nonce' ),
        )
    );

    // Enqueue Font Awesome for icons.
    wp_enqueue_style(
        'font-awesome-css', 
        CHESHIRE_CAT_PLUGIN_URL . 'assets/css/font-awesome/all.min.css', 
        array(), 
        $version
    );

    // Add dynamic CSS based on user settings.
    wp_add_inline_style( 'cheshire-chat-css', cheshirecat_generate_dynamic_css() );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\cheshirecat_enqueue_scripts' );

/**
 * Enqueue scripts and styles for the admin playground page.
 *
 * @since 0.4
 * @return void
 */
function cheshirecat_admin_enqueue_scripts($hook) {
    // Debug: Print the hook name to help identify the correct hook
    if (is_admin() && strpos($hook, 'cheshire') !== false) {
        add_action('admin_footer', function() use ($hook) {
            echo '<!-- Current hook: ' . esc_html($hook) . ' -->';
        });
    }

    // Only enqueue on the playground page
    // Use a more flexible check to match any hook containing both 'cheshire' and 'playground'
    if (strpos($hook, 'cheshire') === false || strpos($hook, 'playground') === false) {
        return;
    }

    $version = CHESHIRE_CAT_VERSION;

    // Enqueue main chat script.
    wp_enqueue_script(
        'cheshire-chat-js', 
        CHESHIRE_CAT_PLUGIN_URL . 'assets/js/chat.js', 
        array( 'jquery' ), 
        $version, 
        true
    );

    // Enqueue main chat styles.
    wp_enqueue_style(
        'cheshire-chat-css', 
        CHESHIRE_CAT_PLUGIN_URL . 'assets/css/chat.css', 
        array(), 
        $version
    );

    // Localize script with AJAX data.
    wp_localize_script(
        'cheshire-chat-js', 
        'cheshire_ajax_object', 
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cheshire_ajax_nonce' ),
        )
    );

    // Enqueue Font Awesome for icons.
    wp_enqueue_style(
        'font-awesome-css', 
        CHESHIRE_CAT_PLUGIN_URL . 'assets/css/font-awesome/all.min.css', 
        array(), 
        $version
    );

    // Add dynamic CSS based on user settings.
    wp_add_inline_style( 'cheshire-chat-css', cheshirecat_generate_dynamic_css() );

    // Add custom CSS for the playground
    $playground_css = "
        #cheshire-chat-container.playground {
            position: relative;
            max-width: 100%;
            width: 100%;
            height: calc(100vh - 150px);
            margin: 20px 0;
            right: auto;
            bottom: auto;
            z-index: 1;
        }
        #cheshire-chat-container.playground #cheshire-chat-messages {
            height: calc(100% - 70px);
        }
        #cheshire-chat-container.playground.with-avatar {
            margin-bottom: 90px;
        }
        #cheshire-chat-container.playground #cheshire-chat-avatar {
            bottom: -70px;
            right: 30px;
            width: 60px;
            height: 60px;
        }
        .playground-header {
            margin-bottom: 20px;
        }
    ";
    wp_add_inline_style( 'cheshire-chat-css', $playground_css );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\cheshirecat_admin_enqueue_scripts' );

/**
 * Generate dynamic CSS based on the saved style settings.
 *
 * @since 0.2
 * @return string The generated CSS.
 */
function cheshirecat_generate_dynamic_css() {
    // Get saved style options with defaults.
    $chat_background_color   = get_option( 'cheshire_chat_background_color', '#ffffff' );
    $chat_text_color         = get_option( 'cheshire_chat_text_color', '#333333' );
    $chat_user_message_color = get_option( 'cheshire_chat_user_message_color', '#4caf50' );
    $chat_bot_message_color  = get_option( 'cheshire_chat_bot_message_color', '#ffffff' );
    $chat_button_color       = get_option( 'cheshire_chat_button_color', '#0078d7' );
    $chat_font_family        = get_option( 'cheshire_chat_font_family', 'Arial, sans-serif' );

    // Build the custom CSS.
    $custom_css = "
        #cheshire-chat-container {
            background-color: " . esc_attr( $chat_background_color ) . ";
            font-family: " . esc_attr( $chat_font_family ) . ";
        }
        #cheshire-chat-messages {
            background-color: " . esc_attr( $chat_background_color ) . ";
        }
        .user-message {
            background-color: " . esc_attr( $chat_user_message_color ) . ";
            color: #fff;
        }
        .bot-message {
            background-color: " . esc_attr( $chat_bot_message_color ) . ";
            color: " . esc_attr( $chat_text_color ) . ";
        }
        #cheshire-chat-send {
            color: " . esc_attr( $chat_button_color ) . ";
        }
        #cheshire-chat-input {
            color: " . esc_attr( $chat_text_color ) . ";
        }
        .error-message {
            color: " . esc_attr( $chat_text_color ) . ";
        }
        #cheshire-chat-container.with-avatar:after {
            background-color: " . esc_attr( $chat_background_color ) . ";
        }
    ";

    return $custom_css;
}

/**
 * Display the welcome message in the chat.
 *
 * @since 0.2
 * @return void
 */
function cheshirecat_display_welcome_message() {
    $welcome_message = get_option( 'cheshire_chat_welcome_message', __( 'Hello! How can I help you?', 'cheshire-cat-chatbot' ) );
    echo '<div class="bot-message"><p>' . esc_html( $welcome_message ) . '</p></div>';
}
