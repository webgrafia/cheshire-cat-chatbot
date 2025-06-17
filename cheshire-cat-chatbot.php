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
 * Version:           0.6.4
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
define( 'CHESHIRE_CAT_VERSION', '0.6.4' );
define( 'CHESHIRE_CAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CHESHIRE_CAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load required files.
require_once CHESHIRE_CAT_PLUGIN_DIR . 'vendor/autoload.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/admin.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/shortcodes.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/ajax.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/helpers.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/meta-boxes.php';
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

    // Get current page/post ID
    $current_page_id = 0;
    if (is_singular()) {
        global $post;
        if ($post) {
            $current_page_id = $post->ID;
        }
    }

    // Get chat settings
    $default_state = get_option('cheshire_plugin_default_state', 'open');

    // Localize script with AJAX data.
    wp_localize_script(
        'cheshire-chat-js', 
        'cheshire_ajax_object', 
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cheshire_ajax_nonce' ),
            'page_id'  => $current_page_id,
            'default_state' => $default_state,
        )
    );

    // Also set the page ID as a global JavaScript variable for backward compatibility
    wp_add_inline_script('cheshire-chat-js', 'window.cheshire_page_id = ' . $current_page_id . ';', 'before');

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

    // Get current page/post ID (in admin, this will be 0)
    $current_page_id = 0;

    // Get chat settings
    $default_state = get_option('cheshire_plugin_default_state', 'open');

    // Localize script with AJAX data.
    wp_localize_script(
        'cheshire-chat-js', 
        'cheshire_ajax_object', 
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cheshire_ajax_nonce' ),
            'page_id'  => $current_page_id,
            'default_state' => $default_state,
        )
    );

    // Also set the page ID as a global JavaScript variable for backward compatibility
    wp_add_inline_script('cheshire-chat-js', 'window.cheshire_page_id = ' . $current_page_id . ';', 'before');

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
    $chat_header_color       = get_option( 'cheshire_chat_header_color', '#ffffff' );
    $chat_font_family        = get_option( 'cheshire_chat_font_family', 'Arial, sans-serif' );

    // Build the custom CSS.
    $custom_css = "
         :root {
                --chat-primary-color: " . esc_attr( $chat_button_color ) . ";
                --chat-primary-hover: " . esc_attr( $chat_bot_message_color ) . ";
                --chat-primary-active: " . esc_attr( $chat_user_message_color ) . ";
          }

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
        #cheshire-chat-header {
            background-color: " . esc_attr( $chat_header_color ) . ";
        }

        #cheshire-chat-close, #cheshire-chat-new {
            color: " . esc_attr( $chat_button_color ) . ";
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

/**
 * Register TinyMCE plugin for Cheshire Cat Chatbot.
 *
 * @since 0.6
 * @return void
 */
function cheshirecat_register_tinymce_plugin($plugins) {
    $plugins['cheshire_cat'] = CHESHIRE_CAT_PLUGIN_URL . 'assets/js/tinymce-plugin.js';
    return $plugins;
}
add_filter('mce_external_plugins', __NAMESPACE__ . '\cheshirecat_register_tinymce_plugin');

/**
 * Add Cheshire Cat button to TinyMCE editor.
 *
 * @since 0.6
 * @return void
 */
function cheshirecat_add_tinymce_button($buttons) {
    array_push($buttons, 'cheshire_cat');
    return $buttons;
}
add_filter('mce_buttons', __NAMESPACE__ . '\cheshirecat_add_tinymce_button');

/**
 * Enqueue scripts and localize data for TinyMCE plugin.
 *
 * @since 0.6
 * @return void
 */
function cheshirecat_tinymce_enqueue_scripts() {
    // Only enqueue on post edit screens
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, array('post', 'page'))) {
        return;
    }

    // Localize script with AJAX data
    wp_localize_script(
        'jquery',
        'cheshire_tinymce_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cheshire_ajax_nonce'),
            'plugin_url' => CHESHIRE_CAT_PLUGIN_URL,
        )
    );
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\cheshirecat_tinymce_enqueue_scripts');

/**
 * Display predefined responses at the end of content for enabled post types.
 *
 * @since 0.7
 * @param string $content The post content.
 * @return string The modified content with predefined responses.
 */
function cheshirecat_add_predefined_responses_to_content( $content ) {
    // Check if the option is enabled
    $show_predefined_in_content = get_option( 'cheshire_plugin_show_predefined_in_content', 'off' );
    if ( $show_predefined_in_content !== 'on' ) {
        return $content;
    }

    // Only show on singular posts/pages
    if ( ! is_singular() ) {
        return $content;
    }

    if (  is_product() ) {
        return $content;
    }

    // Get current post type
    $post_type = get_post_type();

    // Check if current post type is enabled
    $enabled_post_types = get_option( 'cheshire_plugin_enabled_post_types', array( 'post', 'page' ) );
    if ( ! in_array( $post_type, $enabled_post_types ) ) {
        return $content;
    }

    // Get current post ID
    $post_id = get_the_ID();

    // Get predefined responses with post override support
    $responses = cheshirecat_get_predefined_responses_with_override( $post_id );
    if ( empty( $responses ) ) {
        return $content;
    }

    // Get the title for the predefined responses section
    $title = get_option( 'cheshire_plugin_predefined_responses_title', __( 'Frequently Asked Questions', 'cheshire-cat-chatbot' ) );
    $title = apply_filters( 'cheshire_predefined_responses_title', $title );

    // Build the HTML for predefined responses
    $html = '<div id="cheshire-predefined-responses-content" class="cheshire-predefined-responses-content" data-title="' . esc_attr( $title ) . '">';
    foreach ( $responses as $response ) {
        $html .= '<span class="predefined-response-tag content-response-tag">' . esc_html( $response ) . '</span>';
    }
    $html .= '</div>';

    // Add the HTML to the content
    return $content . $html;
}
add_filter( 'the_content', __NAMESPACE__ . '\cheshirecat_add_predefined_responses_to_content' );

/**
 * Display predefined responses after the WooCommerce product short description.
 *
 * @since 0.7
 * @return void
 */
function cheshirecat_add_predefined_responses_after_product_short_description() {
    // Check if WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Check if the option is enabled
    $show_predefined_in_content = get_option( 'cheshire_plugin_show_predefined_in_content', 'off' );
    if ( $show_predefined_in_content !== 'on' ) {
        return;
    }

    // Only show on product pages
    if ( ! is_product() ) {
        return;
    }

    // Check if product post type is enabled
    $enabled_post_types = get_option( 'cheshire_plugin_enabled_post_types', array( 'post', 'page' ) );
    if ( ! in_array( 'product', $enabled_post_types ) ) {
        return;
    }

    // Get current product ID
    $post_id = get_the_ID();

    // Get predefined responses with post override support
    $responses = cheshirecat_get_predefined_responses_with_override( $post_id );
    if ( empty( $responses ) ) {
        return;
    }

    // Get the title for the predefined responses section
    $title = get_option( 'cheshire_plugin_predefined_responses_title', __( 'Frequently Asked Questions', 'cheshire-cat-chatbot' ) );
    $title = apply_filters( 'cheshire_predefined_responses_title', $title );

    // Build the HTML for predefined responses
    $html = '<div id="cheshire-predefined-responses-content" class="cheshire-predefined-responses-content" data-title="' . esc_attr( $title ) . '">';
    foreach ( $responses as $response ) {
        $html .= '<span class="predefined-response-tag content-response-tag">' . esc_html( $response ) . '</span>';
    }
    $html .= '</div>';

    // Output the HTML directly after the short description
    echo $html;
}
// Hook into woocommerce_single_product_summary with priority 40 (well after short description which is 20)
add_action( 'woocommerce_after_add_to_cart_form', __NAMESPACE__ . '\cheshirecat_add_predefined_responses_after_product_short_description', 400 );
