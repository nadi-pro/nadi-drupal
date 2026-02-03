<?php

namespace Nadi\Drupal\Tests\Unit;

use Nadi\Drupal\Tests\TestCase;
use Nadi\Drupal\Transporter;

class TransporterTest extends TestCase
{
    public function test_transporter_can_be_instantiated(): void
    {
        $config = $this->getNadiConfig();
        $transporter = new Transporter($config);

        $this->assertInstanceOf(Transporter::class, $transporter);
    }

    public function test_transporter_configures_log_driver(): void
    {
        $config = $this->getNadiConfig(['driver' => 'log']);
        $transporter = new Transporter($config);

        $service = $transporter->getService();
        $this->assertNotNull($service);
    }

    public function test_transporter_throws_on_invalid_driver(): void
    {
        $this->expectException(\Exception::class);

        $config = $this->getNadiConfig(['driver' => 'nonexistent']);
        new Transporter($config);
    }

    public function test_transporter_store_returns_without_error(): void
    {
        $config = $this->getNadiConfig();
        $transporter = new Transporter($config);

        $transporter->store(['test' => 'data']);
        $this->assertTrue(true);
    }
}
