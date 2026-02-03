<?php

namespace Nadi\Drupal;

use Drupal\Core\Config\ConfigFactoryInterface;
use Nadi\Data\Type;

class Nadi
{
    private Transporter $transporter;

    private array $config;

    public function __construct(ConfigFactoryInterface $configFactory)
    {
        $this->config = $configFactory->get('nadi.settings')->getRawData();
        $this->transporter = new Transporter($this->config);
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function store(array $data): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->transporter->store($data);
    }

    public function recordException(\Throwable $exception): void
    {
        $entry = new Data\ExceptionEntry($exception, Type::EXCEPTION, [
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage(),
        ]);
        $this->store($entry->toArray());
    }

    public function recordQuery(string $sql, float $duration, string $connectionName = 'default'): void
    {
        $entry = new Data\Entry(Type::QUERY, [
            'connection' => $connectionName,
            'sql' => $sql,
            'duration' => $duration,
            'slow' => $duration >= ($this->config['query']['slow_threshold'] ?? 500),
        ]);
        $this->store($entry->toArray());
    }

    public function send(): void
    {
        $this->transporter->send();
    }

    public function test()
    {
        return $this->transporter->test();
    }

    public function verify()
    {
        return $this->transporter->verify();
    }

    public function getTransporter(): Transporter
    {
        return $this->transporter;
    }
}
