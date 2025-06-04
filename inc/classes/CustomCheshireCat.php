<?php
/**
 * Custom Cheshire Cat client implementation
 *
 * @package CheshireCatChatbot
 */

namespace webgrafia\cheshirecat\inc\classes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use CheshireCatSdk\CheshireCat;

/**
 * Custom implementation of the Cheshire Cat client.
 *
 * This class extends the base CheshireCat class from the SDK and provides
 * custom functionality for the WordPress plugin.
 *
 * @since 0.1
 */
class Custom_Cheshire_Cat extends CheshireCat {
    /**
     * Page ID for context information.
     *
     * @since 0.4.2
     * @var int
     */
    protected $page_id = 0;

    /**
     * Page URL for context information.
     *
     * @since 0.4.2
     * @var string
     */
    protected $page_url = '';

    /**
     * Set the page context information.
     *
     * @since 0.4.2
     * @param int    $page_id  The ID of the current page.
     * @param string $page_url The URL of the current page.
     * @return void
     */
    public function setPageContext($page_id, $page_url) {
        $this->page_id = $page_id;
        $this->page_url = $page_url;
    }

    /**
     * Get context information about the current WordPress page.
     *
     * @since 0.5
     * @return string The context information formatted as a string.
     */
    protected function get_context_information() {
        $context = "## Origin of request, simulate you can read it online: \n";
        $context .= "website: " . get_bloginfo('name') . "\n";

        // Get the post object based on page_id if available
        $post = null;
        $post_type = '';
        $is_woocommerce_product = false;

        if ($this->page_id > 0) {
            $post = get_post($this->page_id);
            if ($post) {
                $post_type = $post->post_type;
                $is_woocommerce_product = function_exists('wc_get_product') && $post_type === 'product';
            }
        } else {
            // Try to get the current post if we're not in an AJAX context
            global $post;
        }

        // Add page URL if available
        if (!empty($this->page_url)) {
            $context .= "url: " . $this->page_url . "\n";
        }

        // Determine page type
        if ($post) {
            if ($post_type === 'post') {
                $context .= "pagetype: post\n";
            } elseif ($post_type === 'page') {
                // Check if it's the front page
                if (get_option('page_on_front') == $post->ID) {
                    $context .= "pagetype: homepage\n";
                } else {
                    $context .= "pagetype: page\n";
                }
            } elseif ($is_woocommerce_product) {
                $context .= "pagetype: woocommerce_product\n";
            } else {
                $context .= "pagetype: " . $post_type . "\n";
            }

            // Get title - use post title directly
            $title = $post->post_title;
            $context .= "title: " . wp_strip_all_tags($title) . "\n";

            // Get content/description
            if ($post_type === 'post' || $post_type === 'page') {
                // For posts and pages, get excerpt or content
                if (!empty($post->post_excerpt)) {
                    $context .= "content: " . wp_strip_all_tags($post->post_excerpt) . "\n";
                } else if (!empty($post->post_content)) {
                    // Get the full content
                    $content = $post->post_content;

                    // Remove shortcodes
                    $content = strip_shortcodes($content);

                    // Trim to a reasonable length
                    $excerpt = wp_trim_words($content, 100, '...');
                    $context .= "content: " . wp_strip_all_tags($excerpt) . "\n";
                }

                // Add categories and tags for posts
                if ($post_type === 'post') {
                    $categories = get_the_category($post->ID);
                    if (!empty($categories)) {
                        $category_names = array_map(function($cat) {
                            return $cat->name;
                        }, $categories);
                        $context .= "categories: " . implode(', ', $category_names) . "\n";
                    }

                    $tags = get_the_tags($post->ID);
                    if (!empty($tags)) {
                        $tag_names = array_map(function($tag) {
                            return $tag->name;
                        }, $tags);
                        $context .= "tags: " . implode(', ', $tag_names) . "\n";
                    }
                }
            }

            // For WooCommerce products, add additional information
            if ($is_woocommerce_product) {
                $product = wc_get_product($post->ID);
                if ($product) {
                    // Get product description
                    $product_description = $product->get_description();
                    if (empty($product_description)) {
                        $product_description = $product->get_short_description();
                    }
                    if (!empty($product_description)) {
                        $context .= "content: " . wp_strip_all_tags($product_description) . "\n";
                    }

                    // Get price
                    $context .= "price: " . wp_strip_all_tags($product->get_price_html()) . "\n";

                    // Get product categories
                    $product_categories = wc_get_product_category_list($post->ID);
                    if (!empty($product_categories)) {
                        $context .= "categories: " . wp_strip_all_tags($product_categories) . "\n";
                    }

                    // Get product variations if it's a variable product
                    if ($product->is_type('variable')) {
                        $variations = $product->get_available_variations();
                        $variation_info = "";
                        foreach ($variations as $variation) {
                            $variation_product = wc_get_product($variation['variation_id']);
                            $attributes = $variation_product->get_variation_attributes();
                            $variation_info .= "- ";
                            foreach ($attributes as $attribute_name => $attribute_value) {
                                $taxonomy = str_replace('attribute_', '', $attribute_name);
                                $term = get_term_by('slug', $attribute_value, $taxonomy);
                                $attribute_label = wc_attribute_label($taxonomy);
                                $variation_info .= $attribute_label . ": " . ($term ? $term->name : $attribute_value) . ", ";
                            }
                            $variation_info .= "Price: " . $variation_product->get_price_html() . "\n";
                        }
                        $context .= "variants: \n" . $variation_info;
                    }
                }
            }
        } else {
            // Try to determine page type using WordPress conditional functions
            // These may not work in AJAX context, but we'll try anyway

            if (function_exists('is_archive') && is_archive()) {
                $context .= "pagetype: archive\n";

                // Get archive title
                if (function_exists('get_the_archive_title')) {
                    $title = get_the_archive_title();
                    if (!empty($title)) {
                        $context .= "title: " . wp_strip_all_tags($title) . "\n";
                    }
                }

                // Get archive description
                if (function_exists('get_the_archive_description')) {
                    $description = get_the_archive_description();
                    if (!empty($description)) {
                        $context .= "content: " . wp_strip_all_tags($description) . "\n";
                    }
                }
            } else if (function_exists('is_search') && is_search()) {
                $context .= "pagetype: search\n";
                $context .= "title: " . sprintf(__('Search Results for: %s', 'cheshire-cat-chatbot'), get_search_query()) . "\n";
                $context .= "search_query: " . get_search_query() . "\n";
            } else if (function_exists('is_front_page') && is_front_page()) {
                $context .= "pagetype: homepage\n";
                $context .= "title: " . wp_strip_all_tags(get_bloginfo('name')) . "\n";
                $context .= "content: " . wp_strip_all_tags(get_bloginfo('description')) . "\n";
            } else {
                // Fallback for when we can't determine the page type
                $context .= "pagetype: unknown\n";

                // Try to get title from current page
                $title = wp_get_document_title();
                if (!empty($title)) {
                    $context .= "title: " . wp_strip_all_tags($title) . "\n";
                }

                // Try to extract information from URL
                if (!empty($this->page_url)) {
                    $parsed_url = parse_url($this->page_url);
                    if (isset($parsed_url['path'])) {
                        $path = trim($parsed_url['path'], '/');
                        $path_parts = explode('/', $path);
                        if (!empty($path_parts)) {
                            $context .= "path: " . implode('/', $path_parts) . "\n";
                        }
                    }
                }
            }
        }

        return $context;
    }
    /**
     * Base URL for the Cheshire Cat API.
     *
     * @since 0.1
     * @var string
     */
    protected $base_url;

    /**
     * Authentication token for the Cheshire Cat API.
     *
     * @since 0.1
     * @var string
     */
    protected $token;

    /**
     * HTTP client instance.
     *
     * @since 0.1
     * @var Custom_Cheshire_Cat_Client
     */
    protected $client;

    /**
     * Constructor.
     *
     * @since 0.1
     * @param string $base_url The base URL for the Cheshire Cat API.
     * @param string $token    The authentication token.
     */
    public function __construct( $base_url, $token ) {
        $this->base_url = $base_url;
        $this->token    = $token;
        $this->client   = $this->create_client();
    }

    /**
     * Create a new client instance.
     *
     * @since 0.1
     * @return Custom_Cheshire_Cat_Client The client instance.
     */
    protected function create_client() {
        return new Custom_Cheshire_Cat_Client( $this->base_url, $this->token );
    }

    /**
     * Send a message to the Cheshire Cat API.
     *
     * @since 0.1
     * @param string $message The message to send.
     * @param array  $options Additional options for the request.
     * @return array The response from the API.
     */
    public function sendMessage( string $message, array $options = [] ): array {
        // Get user_id: if user is logged in, use username, otherwise use a cookie-based identifier
        $user_id = 'wp';

        // Check if user is logged in
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $user_id = $current_user->user_login;
        } else {
            // For non-logged in users, use a cookie-based identifier
            $cookie_name = 'cheshire_cat_user_id';

            if ( isset( $_COOKIE[$cookie_name] ) ) {
                $user_id = sanitize_text_field( $_COOKIE[$cookie_name] );
            } else {
                // Generate a unique ID
                $user_id = 'guest_' . uniqid();

                // Set cookie to expire in 30 days
                setcookie( $cookie_name, $user_id, time() + ( 86400 * 30 ), '/' );
            }
        }

        // Check if context information is enabled
        $enable_context = get_option('cheshire_plugin_enable_context', 'off');

        // Append context information to the message if enabled
        if ($enable_context === 'on') {
            $context_info = $this->get_context_information();
            $message .= "\n\n" . $context_info;
        }

        $payload = [
            'text' => $message,
        ];

        // Add user_id to headers instead of payload
        $headers = [
            'user_id' => $user_id,
        ];

        try {
            $response = $this->client->sendMessage( $payload, $headers );

            if ( is_null( $response ) ) {
                // Log the error if WP_DEBUG is enabled.
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'Cheshire Cat API returned null response' );
                }
                return [];
            }

            return json_decode( $response->getBody()->getContents(), true );
        } catch ( \Exception $e ) {
            // Log the error if WP_DEBUG is enabled.
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Cheshire Cat API error: ' . $e->getMessage() );
            }
            return [];
        }
    }

    /**
     * Get the status of the Cheshire Cat API.
     *
     * @since 0.1
     * @return array The status information.
     */
    public function getStatus(): array {
        try {
            $response = $this->client->getStatus();

            if ( is_null( $response ) ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'Cheshire Cat API status check returned null response' );
                }
                return [];
            }

            return json_decode( $response->getBody()->getContents(), true );
        } catch ( \Exception $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Cheshire Cat API status check error: ' . $e->getMessage() );
            }
            return [];
        }
    }

    /**
     * Get available plugins from the Cheshire Cat API.
     *
     * @since 0.1
     * @return array The available plugins.
     */
    public function getAvailablePlugins(): array {
        try {
            $response = $this->client->getAvailablePlugins();

            if ( is_null( $response ) ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'Cheshire Cat API plugins check returned null response' );
                }
                return [];
            }

            return json_decode( $response->getBody()->getContents(), true );
        } catch ( \Exception $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Cheshire Cat API plugins check error: ' . $e->getMessage() );
            }
            return [];
        }
    }
}
