<?php

namespace Nadi\Drupal;

use Nadi\Sampling\Config;
use Nadi\Sampling\FixedRateSampling;
use Nadi\Sampling\SamplingManager;
use Nadi\Transporter\Contract;
use Nadi\Transporter\Service;

class Transporter
{
    protected string $driver;

    protected Contract $transporter;

    protected SamplingManager $samplingManager;

    protected Service $service;

    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->configureTransporter();
        $this->configureSampling();
        $this->service = new Service($this->transporter, $this->samplingManager);
    }

    private function configureTransporter(): void
    {
        $driverName = $this->config['driver'] ?? 'log';
        $this->driver = '\\Nadi\\Transporter\\'.ucfirst($driverName);

        if (! class_exists($this->driver)) {
            throw new \Exception("$this->driver did not exists");
        }

        if (! in_array(Contract::class, class_implements($this->driver))) {
            throw new \Exception("$this->driver did not implement the \Nadi\Transporter\Contract class.");
        }

        $connections = $this->config['connections'] ?? [];
        $this->transporter = (new $this->driver)
            ->configure($connections[$driverName] ?? []);
    }

    private function configureSampling(): void
    {
        $samplingConfig = $this->config['sampling'] ?? [];
        $samplingParams = $samplingConfig['config'] ?? [];

        $config = new Config(
            samplingRate: $samplingParams['sampling_rate'] ?? 0.1,
            baseRate: $samplingParams['base_rate'] ?? 0.05,
            loadFactor: $samplingParams['load_factor'] ?? 1.0,
            intervalSeconds: $samplingParams['interval_seconds'] ?? 60
        );

        $strategy = $samplingConfig['strategy'] ?? 'fixed_rate';
        $strategies = [
            'dynamic_rate' => \Nadi\Sampling\DynamicRateSampling::class,
            'fixed_rate' => FixedRateSampling::class,
            'interval' => \Nadi\Sampling\IntervalSampling::class,
            'peak_load' => \Nadi\Sampling\PeakLoadSampling::class,
        ];

        $class = $strategies[$strategy] ?? FixedRateSampling::class;

        $this->samplingManager = new SamplingManager(new $class($config));
    }

    public function store(array $data)
    {
        return $this->service->handle($data);
    }

    public function send()
    {
        return $this->service->send();
    }

    public function test()
    {
        return $this->service->test();
    }

    public function verify()
    {
        return $this->service->verify();
    }

    public function getService(): ?Service
    {
        return $this->service ?? null;
    }

    public function __destruct()
    {
        return $this->service->send();
    }
}
