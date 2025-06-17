<?php
/**
 * Meta boxes for Cheshire Cat Chatbot
 *
 * @package CheshireCatChatbot
 */

namespace webgrafia\cheshirecat;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register meta boxes for enabled post types.
 *
 * @since 0.7.1
 * @return void
 */
function cheshirecat_register_meta_boxes() {
    // Get enabled post types
    $enabled_post_types = get_option( 'cheshire_plugin_enabled_post_types', array( 'post', 'page' ) );
    
    // Register meta box for each enabled post type
    foreach ( $enabled_post_types as $post_type ) {
        add_meta_box(
            'cheshire_predefined_responses_meta_box',
            __( 'Cheshire Cat Predefined Questions', 'cheshire-cat-chatbot' ),
            __NAMESPACE__ . '\cheshirecat_predefined_responses_meta_box_callback',
            $post_type,
            'normal',
            'default'
        );
    }
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\cheshirecat_register_meta_boxes' );

/**
 * Render the predefined responses meta box.
 *
 * @since 0.7.1
 * @param WP_Post $post The post object.
 * @return void
 */
function cheshirecat_predefined_responses_meta_box_callback( $post ) {
    // Add nonce for security
    wp_nonce_field( 'cheshire_predefined_responses_meta_box', 'cheshire_predefined_responses_meta_box_nonce' );
    
    // Get the current value
    $value = get_post_meta( $post->ID, '_cheshire_predefined_responses', true );
    
    // Get the global value for reference
    $global_value = get_option( 'cheshire_plugin_predefined_responses', '' );
    
    // Output the meta box HTML
    ?>
    <p><?php esc_html_e( 'Enter predefined questions for this specific post/page, one per line. These will override the global predefined questions.', 'cheshire-cat-chatbot' ); ?></p>
    <textarea name="cheshire_predefined_responses" id="cheshire_predefined_responses" rows="5" style="width: 100%;"><?php echo esc_textarea( $value ); ?></textarea>
    
    <?php if ( ! empty( $global_value ) ) : ?>
        <div style="margin-top: 10px;">
            <p><strong><?php esc_html_e( 'Global Predefined Questions (for reference):', 'cheshire-cat-chatbot' ); ?></strong></p>
            <div style="background: #f8f8f8; padding: 10px; border: 1px solid #ddd;">
                <?php echo nl2br( esc_html( $global_value ) ); ?>
            </div>
        </div>
    <?php endif; ?>
    <?php
}

/**
 * Save the meta box data.
 *
 * @since 0.7.1
 * @param int $post_id The post ID.
 * @return void
 */
function cheshirecat_save_predefined_responses_meta_box( $post_id ) {
    // Check if our nonce is set
    if ( ! isset( $_POST['cheshire_predefined_responses_meta_box_nonce'] ) ) {
        return;
    }
    
    // Verify that the nonce is valid
    if ( ! wp_verify_nonce( $_POST['cheshire_predefined_responses_meta_box_nonce'], 'cheshire_predefined_responses_meta_box' ) ) {
        return;
    }
    
    // If this is an autosave, our form has not been submitted, so we don't want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    // Check the user's permissions
    if ( isset( $_POST['post_type'] ) ) {
        if ( 'page' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }
    }
    
    // Sanitize user input
    $predefined_responses = isset( $_POST['cheshire_predefined_responses'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cheshire_predefined_responses'] ) ) : '';
    
    // Update the meta field in the database
    update_post_meta( $post_id, '_cheshire_predefined_responses', $predefined_responses );
}
add_action( 'save_post', __NAMESPACE__ . '\cheshirecat_save_predefined_responses_meta_box' );