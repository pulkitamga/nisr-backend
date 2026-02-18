<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
class UcmApiService
{
    protected Client $client;
    protected array $config;

    public function __construct()
    {
        $this->config = ucmConfig();
        if (!$this->config['status']) return;

        $this->client = new Client([
            'base_uri' => "https://{$this->config['host']}:{$this->config['port']}/api/",
            'timeout'  => 8,
            'verify'   => false,
        ]);
    }

    public function request(array $payload): array
    {
        $options = ['json' => ['request' => $payload]];
        if ($this->config['digest']) {
            $options['auth'] = [$this->config['username'], $this->config['password'], 'digest'];
        }

        try {
            $res = $this->client->post('', $options);
            return json_decode($res->getBody(), true);
        } catch (\Exception $e) {
            Log::error('UCM API Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getActiveCalls(): array
    {
        $res = $this->request(['action' => 'listActiveCalls']);
        return $res['response']['channels'] ?? [];
    }
}