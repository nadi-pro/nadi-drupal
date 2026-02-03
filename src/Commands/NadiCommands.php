<?php

namespace Nadi\Drupal\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Nadi\Drupal\Nadi;
use Nadi\Drupal\Shipper\Shipper;

class NadiCommands extends DrushCommands
{
    public function __construct(
        private Nadi $nadi,
    ) {
        parent::__construct();
    }

    #[CLI\Command(name: 'nadi:install', aliases: ['nadi-install'])]
    #[CLI\Help(description: 'Install and configure Nadi monitoring.')]
    public function install(): void
    {
        $this->io()->title('Installing Nadi Monitoring');

        // Install shipper binary.
        $this->io()->section('Installing Shipper Binary');

        try {
            Shipper::install();
            $this->io()->success('Shipper binary installed successfully.');
        } catch (\Exception $e) {
            $this->io()->warning('Could not install shipper binary: '.$e->getMessage());
            $this->io()->note('You can install it later with: drush nadi:update-shipper');
        }

        // Environment variables reminder.
        $this->io()->section('Environment Configuration');
        $this->io()->text([
            'Configure the following in your Drupal settings or admin UI:',
            '',
            'Visit /admin/config/system/nadi to configure:',
            '  - API key (Sanctum token)',
            '  - Application key',
            '  - Transport driver (log, http, opentelemetry)',
            '',
            'Get your keys at: https://nadi.pro',
        ]);

        $this->io()->success('Nadi monitoring has been installed successfully!');
    }

    #[CLI\Command(name: 'nadi:test', aliases: ['nadi-test'])]
    #[CLI\Help(description: 'Test the Nadi monitoring connection.')]
    public function test(): void
    {
        $this->io()->title('Testing Nadi Connection');

        $transporter = $this->nadi->getTransporter();

        try {
            $result = $transporter->test();

            if ($result) {
                $this->io()->success('Successfully connected to Nadi!');
            } else {
                $this->io()->error('Connection test failed. Please check your configuration.');
            }
        } catch (\Exception $e) {
            $this->io()->error('Connection test failed: '.$e->getMessage());
        }
    }

    #[CLI\Command(name: 'nadi:verify', aliases: ['nadi-verify'])]
    #[CLI\Help(description: 'Verify the Nadi monitoring configuration.')]
    public function verify(): void
    {
        $this->io()->title('Verifying Nadi Configuration');

        $config = $this->nadi->getConfig();
        $errors = [];
        $warnings = [];

        // Check if enabled.
        $enabled = $config['enabled'] ?? false;
        if (! $enabled) {
            $warnings[] = 'Nadi monitoring is currently disabled.';
        }

        // Check driver.
        $driver = $config['driver'] ?? 'log';
        $this->io()->text("Driver: <info>{$driver}</info>");

        // Check driver-specific configuration.
        $connections = $config['connections'] ?? [];
        $driverConfig = $connections[$driver] ?? [];

        if ($driver === 'http') {
            if (empty($driverConfig['api_key'])) {
                $errors[] = 'HTTP driver requires api_key to be set.';
            }
            if (empty($driverConfig['app_key'])) {
                $errors[] = 'HTTP driver requires app_key to be set.';
            }
        }

        if ($driver === 'log') {
            $logPath = $driverConfig['path'] ?? '';
            if ($logPath) {
                $this->io()->text("Log path: <info>{$logPath}</info>");
            }
        }

        if ($driver === 'opentelemetry') {
            if (empty($driverConfig['endpoint'])) {
                $errors[] = 'OpenTelemetry driver requires an endpoint to be configured.';
            }
        }

        // Check transporter.
        try {
            $transporter = $this->nadi->getTransporter();
            $result = $transporter->verify();
            if ($result) {
                $this->io()->text('Transporter verification: <info>OK</info>');
            } else {
                $errors[] = 'Transporter verification failed.';
            }
        } catch (\Exception $e) {
            $errors[] = 'Transporter verification error: '.$e->getMessage();
        }

        // Report results.
        foreach ($warnings as $warning) {
            $this->io()->warning($warning);
        }

        if (! empty($errors)) {
            foreach ($errors as $error) {
                $this->io()->error($error);
            }

            return;
        }

        $this->io()->success('Nadi configuration is valid!');
    }

    #[CLI\Command(name: 'nadi:update-shipper', aliases: ['nadi-update-shipper'])]
    #[CLI\Help(description: 'Update the Nadi shipper binary.')]
    public function updateShipper(): void
    {
        $this->io()->title('Updating Nadi Shipper');

        try {
            $shipper = new Shipper(dirname(__DIR__, 2));
            $manager = $shipper->getBinaryManager();

            if ($manager->needsUpdate()) {
                $version = $manager->update();
                $this->io()->success("Shipper updated to version: {$version}");
            } else {
                $this->io()->note('Shipper is already up to date.');
            }
        } catch (\Exception $e) {
            $this->io()->error('Failed to update shipper: '.$e->getMessage());
        }
    }
}
