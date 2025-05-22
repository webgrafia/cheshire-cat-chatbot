<?php

namespace webgrafia\cheshirecat\inc\classes;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use CheshireCatSdk\Http\Clients\CheshireCatClient;
use GuzzleHttp\Client;
class CHESHIRECAT_CustomCheshireCatClient extends CheshireCatClient
{
    protected $baseUrl;
    protected $token;

    public function __construct($baseUrl, $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
        //parent::__construct(); // REMOVE THIS LINE!
        $this->client = new Client([
            'base_uri' => $this->getBaseUrl(),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getToken(array $credentials = []): string // Added array $credentials = []
    {
        return $this->token;
    }
}
