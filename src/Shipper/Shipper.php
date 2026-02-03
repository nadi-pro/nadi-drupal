<?php

namespace Nadi\Drupal\Shipper;

use Nadi\Shipper\BinaryManager;

class Shipper
{
    private BinaryManager $binaryManager;

    public function __construct(
        private string $moduleDir,
    ) {
        $this->binaryManager = new BinaryManager(
            $this->moduleDir.'/vendor/bin',
        );
    }

    public static function install(?string $moduleDir = null): void
    {
        $dir = $moduleDir ?? dirname(__DIR__, 2);
        $shipper = new self($dir);

        if ($shipper->isInstalled()) {
            return;
        }

        $shipper->binaryManager->install();
    }

    public function isInstalled(): bool
    {
        return $this->binaryManager->isInstalled();
    }

    public function send(string $configPath): array
    {
        return $this->binaryManager->execute([
            'send',
            '--config',
            $configPath,
        ]);
    }

    public function test(string $configPath): array
    {
        return $this->binaryManager->execute([
            'test',
            '--config',
            $configPath,
        ]);
    }

    public function verify(string $configPath): array
    {
        return $this->binaryManager->execute([
            'verify',
            '--config',
            $configPath,
        ]);
    }

    public function getBinaryManager(): BinaryManager
    {
        return $this->binaryManager;
    }
}
