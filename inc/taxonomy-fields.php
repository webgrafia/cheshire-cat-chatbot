<?php
/**
 * Taxonomy fields for Cheshire Cat Chatbot
 *
 * @package CheshireCatChatbot
 */

namespace webgrafia\cheshirecat;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add custom fields to product category taxonomy.
 *
 * @since 1.0.0
 * @param WP_Term $term The term object.
 * @return void
 */
function cheshirecat_add_product_category_fields( $term ) {
    // Check if this is a product category
    if ( $term->taxonomy !== 'product_cat' ) {
        return;
    }

    // Get the current value
    $predefined_responses = get_term_meta( $term->term_id, '_cheshire_predefined_responses', true );

    // Get the global value for reference
    $global_value = get_option( 'cheshire_plugin_product_category_predefined_responses', '' );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="cheshire_predefined_responses"><?php esc_html_e( 'Cheshire Cat Predefined Questions', 'cheshire-cat-chatbot' ); ?></label>
        </th>
        <td>
            <p><?php esc_html_e( 'Enter predefined questions for this specific product category, one per line. These will override the global product category predefined questions.', 'cheshire-cat-chatbot' ); ?></p>
            <textarea name="cheshire_predefined_responses" id="cheshire_predefined_responses" rows="5" style="width: 100%;"><?php echo esc_textarea( $predefined_responses ); ?></textarea>

            <?php if ( ! empty( $global_value ) ) : ?>
                <div style="margin-top: 10px;">
                    <p><strong><?php esc_html_e( 'Global Product Category Predefined Questions (for reference):', 'cheshire-cat-chatbot' ); ?></strong></p>
                    <div style="background: #f8f8f8; padding: 10px; border: 1px solid #ddd;">
                        <?php echo nl2br( esc_html( $global_value ) ); ?>
                    </div>
                </div>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}
add_action( 'product_cat_edit_form_fields', __NAMESPACE__ . '\cheshirecat_add_product_category_fields', 10, 1 );

/**
 * Save custom fields for product category taxonomy.
 *
 * @since 1.0.0
 * @param int $term_id The term ID.
 * @return void
 */
function cheshirecat_save_product_category_fields( $term_id ) {
    // Check if this is a product category
    if ( ! isset( $_POST['taxonomy'] ) || $_POST['taxonomy'] !== 'product_cat' ) {
        return;
    }

    // Check if our field is set
    if ( isset( $_POST['cheshire_predefined_responses'] ) ) {
        // Sanitize user input
        $predefined_responses = sanitize_textarea_field( wp_unslash( $_POST['cheshire_predefined_responses'] ) );

        // Update the term meta
        update_term_meta( $term_id, '_cheshire_predefined_responses', $predefined_responses );
    }
}
add_action( 'edited_product_cat', __NAMESPACE__ . '\cheshirecat_save_product_category_fields', 10, 1 );
add_action( 'created_product_cat', __NAMESPACE__ . '\cheshirecat_save_product_category_fields', 10, 1 );