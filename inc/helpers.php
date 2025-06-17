<?php
/**
 * Helper functions for Cheshire Cat Chatbot
 *
 * @package CheshireCatChatbot
 */

namespace webgrafia\cheshirecat;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get predefined responses with post override support.
 *
 * This function checks if there are post-specific overrides for predefined responses
 * and returns those if available, otherwise falls back to the global settings.
 *
 * @since 0.7.1
 * @param int $post_id The post ID to check for overrides.
 * @return array The predefined responses as an array.
 */
function cheshirecat_get_predefined_responses_with_override( $post_id = 0 ) {
    $responses = array();
    
    // Check if we have a post ID and if it has overrides
    if ( $post_id > 0 ) {
        $post_specific_responses = get_post_meta( $post_id, '_cheshire_predefined_responses', true );
        
        // If post has specific responses, use those
        if ( ! empty( $post_specific_responses ) ) {
            // Split by newline and filter out empty lines
            $responses = array_filter( explode( "\n", $post_specific_responses ), 'trim' );
            
            // If we have responses, return them
            if ( ! empty( $responses ) ) {
                return $responses;
            }
        }
    }
    
    // Fall back to global responses if no post-specific ones or if they're empty
    $global_responses = get_option( 'cheshire_plugin_predefined_responses', '' );
    
    if ( ! empty( $global_responses ) ) {
        // Split by newline and filter out empty lines
        $responses = array_filter( explode( "\n", $global_responses ), 'trim' );
    }
    
    return $responses;
}