<?php
declare(strict_types=1);

namespace fholbrook\Openrouter;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

class OpenRouterConfig
{
    public function __construct(
        private readonly string $apiKey,
        private readonly int $timeout = 30,
        public readonly ?string $defaultModel = null,
        private readonly ?string $referrerUri = null,
        private readonly ?string $referrerTitle = null,
        private readonly string $baseUri = 'https://openrouter.ai/api/v1/'
    ) {
    }

    public function getClient(): Client
    {
        // Create a handler stack with the retry middleware.
        $handlerStack = HandlerStack::create();

        // Add the retry middleware to the handler stack.
        $handlerStack->push(
            GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 5,
                'retry_on_status'    => [429, 500, 502, 503, 504],
                'retry_on_timeout'   => true,
            ])
        );


        return new Client([
            'base_uri' => rtrim($this->baseUri, '/') . '/',
            'timeout'  => $this->timeout,
            'handler'  => $handlerStack,
            'headers'  => array_filter([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer'  => $this->referrerUri,
                'X-Title'       => $this->referrerTitle,
                'Content-Type'  => 'application/json',
            ]),
        ]);
    }
}