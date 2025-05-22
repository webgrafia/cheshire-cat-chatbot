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
        $payload = [
            'text' => $message,
        ];

        try {
            $response = $this->client->sendMessage( $payload );

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
