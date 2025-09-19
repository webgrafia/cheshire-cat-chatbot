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
 * Version:           0.9.5
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
define( 'CHESHIRE_CAT_VERSION', '0.9.5' );
define( 'CHESHIRE_CAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CHESHIRE_CAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load required files.
require_once CHESHIRE_CAT_PLUGIN_DIR . 'vendor/autoload.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/admin.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/shortcodes.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/ajax.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/helpers.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/meta-boxes.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/declarative-memory.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/taxonomy-fields.php';
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/classes/CustomCheshireCatClient.php'; // Load for backward compatibility
require_once CHESHIRE_CAT_PLUGIN_DIR . 'inc/classes/CustomCheshireCat.php'; // Load for backward compatibility

/**
 * Check if the chatbot should be enabled on the current page.
 *
 * @since 0.8.0
 * @return bool Whether the chatbot should be enabled
 */
function cheshirecat_is_chatbot_enabled_on_page() {
    $cheshire_global_chat = get_option('cheshire_plugin_global_chat');

    // Check if global chat is enabled
    if ($cheshire_global_chat !== 'on') {
        return false;
    }

    // Check if chat should only be shown to logged-in users
    $logged_in_only = get_option('cheshire_plugin_logged_in_only', 'off');
    if ($logged_in_only === 'on' && !is_user_logged_in()) {
        return false;
    }

    // Get content type mode
    $content_type_mode = get_option('cheshire_plugin_content_type_mode', 'site_wide');

    // Check if we're on the homepage
    $show_in_homepage = get_option('cheshire_plugin_show_in_homepage', 'off');
    $is_homepage = is_front_page() || is_home();

    // If we're on the homepage and it's enabled, show the chat
    if ($is_homepage && $show_in_homepage === 'on') {
        return true;
    }

    // If content type mode is site_wide, show the chat on all pages
    if ($content_type_mode === 'site_wide') {
        return true;
    }

    // Otherwise, check if current post type or taxonomy is enabled
    $enabled_post_types = get_option('cheshire_plugin_enabled_post_types', array('post', 'page'));
    $current_post_type = get_post_type();

    // Check if current taxonomy is enabled
    $enabled_taxonomies = get_option('cheshire_plugin_enabled_taxonomies', array('category', 'post_tag'));
    $is_taxonomy_page = false;
    $current_taxonomy = '';

    // Check if we're on a taxonomy archive page
    if (is_tax() || is_category() || is_tag()) {
        $is_taxonomy_page = true;
        $queried_object = get_queried_object();
        if (isset($queried_object->taxonomy)) {
            $current_taxonomy = $queried_object->taxonomy;
        }
    }

    // Only show chat if we're on an enabled post type or taxonomy
    if (
        // For all post types, only show on singular pages
        ($current_post_type && is_singular($current_post_type) && in_array($current_post_type, $enabled_post_types)) ||
        // For taxonomies, only show on taxonomy term pages (detail pages)
        ($is_taxonomy_page && in_array($current_taxonomy, $enabled_taxonomies))
    ) {
        return true;
    }

    return false;
}

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
    $enable_websocket = get_option('cheshire_plugin_enable_websocket', 'off');
    $cheshire_plugin_url = get_option('cheshire_plugin_url', '');
    $cheshire_plugin_websocket_url = get_option('cheshire_plugin_websocket_url', '');
    $cheshire_plugin_token = get_option('cheshire_plugin_token', '');

    // Get context and reinforcement settings
    $enable_context = get_option('cheshire_plugin_enable_context', 'off');
    $enable_reinforcement = get_option('cheshire_plugin_enable_reinforcement', 'off');
    $reinforcement_message = get_option('cheshire_plugin_reinforcement_message', '');

    // Get declarative memory link settings
    $enable_related_links = get_option('cheshire_plugin_enable_related_links', 'off');
    $minimum_link_score = get_option('cheshire_plugin_minimum_link_score', '0.8');
    $link_text = get_option('cheshire_plugin_link_text', 'Related link');

    // Check if we're on a product category page
    $is_product_category = false;
    $product_category_id = 0;
    if (function_exists('is_product_category') && is_product_category()) {
        $is_product_category = true;
        $term = get_queried_object();
        if ($term && isset($term->term_id)) {
            $product_category_id = $term->term_id;
        }
    }

    // Check if chatbot is enabled on this page
    $is_chatbot_enabled = cheshirecat_is_chatbot_enabled_on_page();

    // Localize script with AJAX data.
    wp_localize_script(
        'cheshire-chat-js', 
        'cheshire_ajax_object', 
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cheshire_ajax_nonce' ),
            'page_id'  => $current_page_id,
            'default_state' => $default_state,
            'enable_websocket' => $enable_websocket,
            'cheshire_url' => $cheshire_plugin_url,
            'token' => $cheshire_plugin_token,
            // Add context and reinforcement settings for WebSocket
            'enable_context' => $enable_context,
            'enable_reinforcement' => $enable_reinforcement,
            'reinforcement_message' => $reinforcement_message,
            // Add declarative memory link settings
            'enable_related_links' => $enable_related_links,
            'minimum_link_score' => $minimum_link_score,
            'link_text' => $link_text,
            // Add flag to indicate if chatbot is enabled on this page
            'is_chatbot_enabled' => $is_chatbot_enabled,
            // Add product category information
            'is_product_category' => $is_product_category,
            'product_category_id' => $product_category_id,
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
 * Add websocket URL to JavaScript data
 * 
 * @since 0.7.2
 * @return void
 */
function cheshirecat_add_websocket_url() {
    $cheshire_plugin_websocket_url = get_option('cheshire_plugin_websocket_url', '');
    if (!empty($cheshire_plugin_websocket_url)) {
        wp_add_inline_script('cheshire-chat-js', 'if(typeof cheshire_ajax_object !== "undefined") { cheshire_ajax_object.websocket_url = "' . esc_js($cheshire_plugin_websocket_url) . '"; }', 'before');
    }
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\cheshirecat_add_websocket_url', 20 );

/**
 * Enqueue scripts and styles for the admin pages.
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

    // Enqueue admin styles for all Cheshire Cat admin pages
    if (strpos($hook, 'cheshire') !== false) {
        $version = CHESHIRE_CAT_VERSION;

        // Enqueue admin styles
        wp_enqueue_style(
            'cheshire-admin-css', 
            CHESHIRE_CAT_PLUGIN_URL . 'assets/css/admin.css', 
            array(), 
            $version
        );
    }

    // Only enqueue chat-specific scripts and styles on the playground page
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
    $enable_websocket = get_option('cheshire_plugin_enable_websocket', 'off');
    $cheshire_plugin_url = get_option('cheshire_plugin_url', '');
    $cheshire_plugin_websocket_url = get_option('cheshire_plugin_websocket_url', '');
    $cheshire_plugin_token = get_option('cheshire_plugin_token', '');

    // Get context and reinforcement settings
    $enable_context = get_option('cheshire_plugin_enable_context', 'off');
    $enable_reinforcement = get_option('cheshire_plugin_enable_reinforcement', 'off');
    $reinforcement_message = get_option('cheshire_plugin_reinforcement_message', '');

    // Get declarative memory link settings
    $enable_related_links = get_option('cheshire_plugin_enable_related_links', 'off');
    $minimum_link_score = get_option('cheshire_plugin_minimum_link_score', '0.8');
    $link_text = get_option('cheshire_plugin_link_text', 'Related link');

    // Localize script with AJAX data.
    wp_localize_script(
        'cheshire-chat-js', 
        'cheshire_ajax_object', 
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'cheshire_ajax_nonce' ),
            'page_id'  => $current_page_id,
            'default_state' => $default_state,
            'enable_websocket' => $enable_websocket,
            'cheshire_url' => $cheshire_plugin_url,
            'token' => $cheshire_plugin_token,
            // Add context and reinforcement settings for WebSocket
            'enable_context' => $enable_context,
            'enable_reinforcement' => $enable_reinforcement,
            'reinforcement_message' => $reinforcement_message,
            // Add declarative memory link settings
            'enable_related_links' => $enable_related_links,
            'minimum_link_score' => $minimum_link_score,
            'link_text' => $link_text,
            // In admin playground, the chatbot is always enabled
            'is_chatbot_enabled' => true,
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
 * Add websocket URL to JavaScript data in admin
 * 
 * @since 0.7.2
 * @return void
 */
function cheshirecat_admin_add_websocket_url($hook) {
    // Only add on the playground page
    if (strpos($hook, 'cheshire') === false || strpos($hook, 'playground') === false) {
        return;
    }

    $cheshire_plugin_websocket_url = get_option('cheshire_plugin_websocket_url', '');
    if (!empty($cheshire_plugin_websocket_url)) {
        wp_add_inline_script('cheshire-chat-js', 'if(typeof cheshire_ajax_object !== "undefined") { cheshire_ajax_object.websocket_url = "' . esc_js($cheshire_plugin_websocket_url) . '"; }', 'before');
    }
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\cheshirecat_admin_add_websocket_url', 20 );

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
    // Get current screen
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }

    // Enqueue on post edit screens and taxonomy edit screens
    if (!in_array($screen->base, array('post', 'page', 'term'))) {
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
