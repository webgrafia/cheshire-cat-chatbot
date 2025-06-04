<?php
/**
 * AJAX handlers for Cheshire Cat Chatbot
 *
 * @package CheshireCatChatbot
 */

namespace webgrafia\cheshirecat;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle AJAX request for sending a message to Cheshire Cat.
 *
 * This function processes both 'cheshire_send_message' and 'cheshire_plugin_ajax' actions.
 *
 * @since 0.1
 * @return void
 */
function cheshirecat_process_message() {
    // Verify nonce for security.
    check_ajax_referer( 'cheshire_ajax_nonce', 'nonce' );

    // Check if message is provided.
    if ( ! isset( $_POST['message'] ) ) {
        wp_send_json_error( __( 'Message not provided.', 'cheshire-cat-chatbot' ) );
        return;
    }

    // Sanitize the message.
    $message = sanitize_text_field( wp_unslash( $_POST['message'] ) );

    // Get page information if provided
    $page_id = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
    $page_url = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';

    // Get Cheshire Cat configuration.
    $cheshire_plugin_url   = get_option( 'cheshire_plugin_url' );
    $cheshire_plugin_token = get_option( 'cheshire_plugin_token' );

    // Validate configuration.
    if ( empty( $cheshire_plugin_url ) || empty( $cheshire_plugin_token ) ) {
        wp_send_json_error( __( 'Cheshire Cat URL or Token not set.', 'cheshire-cat-chatbot' ) );
        return;
    }

    // Initialize Cheshire Cat client.
    $cheshire_cat = new inc\classes\Custom_Cheshire_Cat( $cheshire_plugin_url, $cheshire_plugin_token );

    // Set page context information
    $cheshire_cat->setPageContext($page_id, $page_url);

    try {
        // Send message and get response.
        $response = $cheshire_cat->sendMessage( $message );
        wp_send_json_success( $response );
    } catch ( \Exception $e ) {
        // Handle errors.
        wp_send_json_error( $e->getMessage() );
    }
}

// Register AJAX handlers for both logged-in and non-logged-in users.
// Support both action names for backward compatibility.
add_action( 'wp_ajax_cheshire_send_message', __NAMESPACE__ . '\cheshirecat_process_message' );
add_action( 'wp_ajax_nopriv_cheshire_send_message', __NAMESPACE__ . '\cheshirecat_process_message' );
add_action( 'wp_ajax_cheshire_plugin_ajax', __NAMESPACE__ . '\cheshirecat_process_message' );
add_action( 'wp_ajax_nopriv_cheshire_plugin_ajax', __NAMESPACE__ . '\cheshirecat_process_message' );
