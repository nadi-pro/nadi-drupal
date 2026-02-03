<?php

namespace Nadi\Drupal\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getNadiConfig(array $overrides = []): array
    {
        return array_merge([
            'enabled' => true,
            'driver' => 'log',
            'connections' => [
                'log' => [
                    'path' => sys_get_temp_dir().'/nadi-test',
                ],
                'http' => [
                    'api_key' => 'test-api-key',
                    'app_key' => 'test-app-key',
                    'endpoint' => 'https://nadi.pro/api',
                    'version' => 'v1',
                ],
                'opentelemetry' => [
                    'endpoint' => 'http://localhost:4318',
                    'service_name' => 'test-app',
                    'service_version' => '1.0.0',
                    'deployment_environment' => 'test',
                    'suppress_errors' => true,
                ],
            ],
            'query' => [
                'slow_threshold' => 500,
            ],
            'http' => [
                'hidden_request_headers' => ['authorization', 'php-auth-pw'],
                'hidden_parameters' => ['password', 'password_confirmation'],
                'ignored_status_codes' => ['200-307'],
            ],
            'sampling' => [
                'strategy' => 'fixed_rate',
                'config' => [
                    'sampling_rate' => 1.0,
                    'base_rate' => 0.05,
                    'load_factor' => 1.0,
                    'interval_seconds' => 60,
                ],
            ],
        ], $overrides);
    }
}
