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
