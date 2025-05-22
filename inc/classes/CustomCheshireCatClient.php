<?php
/**
 * Custom Cheshire Cat HTTP client
 *
 * @package CheshireCatChatbot
 */

namespace webgrafia\cheshirecat\inc\classes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use CheshireCatSdk\Http\Clients\CheshireCatClient;
use GuzzleHttp\Client;

/**
 * Custom implementation of the Cheshire Cat HTTP client.
 *
 * This class extends the base CheshireCatClient class from the SDK and provides
 * custom functionality for the WordPress plugin.
 *
 * @since 0.1
 */
class Custom_Cheshire_Cat_Client extends CheshireCatClient {
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
     * Constructor.
     *
     * @since 0.1
     * @param string $base_url The base URL for the Cheshire Cat API.
     * @param string $token    The authentication token.
     */
    public function __construct( $base_url, $token ) {
        $this->base_url = $base_url;
        $this->token    = $token;

        // Initialize the HTTP client with proper headers.
        $this->client = new Client( [
            'base_uri' => $this->getBaseUrl(),
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Content-Type'  => 'application/json',
            ],
        ] );
    }

    /**
     * Get the base URL for the Cheshire Cat API.
     *
     * @since 0.1
     * @return string The base URL.
     */
    public function getBaseUrl(): string {
        return $this->base_url;
    }

    /**
     * Get the authentication token.
     *
     * @since 0.1
     * @param array $credentials Optional credentials (not used in this implementation).
     * @return string The authentication token.
     */
    public function getToken( array $credentials = [] ): string {
        return $this->token;
    }
}
