<?php

namespace Nadi\Drupal\Metric;

use Nadi\Metric\Base;

class Network extends Base
{
    public function metrics(): array
    {
        if (PHP_SAPI === 'cli' || ! isset($_SERVER['REQUEST_URI'])) {
            return [];
        }

        return [
            'net.host.name' => $_SERVER['HTTP_HOST'] ?? gethostname(),
            'net.host.port' => $_SERVER['SERVER_PORT'] ?? 80,
            'net.protocol.name' => 'HTTP',
            'net.protocol.version' => $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1',
        ];
    }
}
