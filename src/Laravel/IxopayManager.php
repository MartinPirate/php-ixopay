<?php

namespace Ixopay\Client\Laravel;

use Ixopay\Client\Client;
use Psr\Log\LoggerInterface;
use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;

class IxopayManager
{
    public function __construct(
        private readonly Repository $config,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function client(): Client
    {
        $config = $this->config->get('ixopay', []);

        foreach (['username', 'password', 'api_key', 'shared_secret'] as $key) {
            if (empty($config[$key])) {
                throw new InvalidArgumentException(sprintf('Missing IXOPAY configuration value [%s].', $key));
            }
        }

        $client = new Client(
            (string) $config['username'],
            (string) $config['password'],
            (string) $config['api_key'],
            (string) $config['shared_secret'],
            $config['language'] ?? null
        );

        if (!empty($config['gateway_url'])) {
            Client::setApiUrl($config['gateway_url']);
        }

        if (!empty($config['custom_request_headers']) && is_array($config['custom_request_headers'])) {
            $client->setCustomRequestHeaders($config['custom_request_headers']);
        }

        if (!empty($config['custom_curl_options']) && is_array($config['custom_curl_options'])) {
            $client->setCustomCurlOptions($config['custom_curl_options']);
        }

        if ($this->logger instanceof LoggerInterface) {
            $client->setLogger($this->logger);
        }

        return $client;
    }
}
