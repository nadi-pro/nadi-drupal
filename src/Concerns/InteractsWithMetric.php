<?php

namespace Nadi\Drupal\Concerns;

use Nadi\Drupal\Metric\Application;
use Nadi\Drupal\Metric\Framework;
use Nadi\Drupal\Metric\Http;
use Nadi\Drupal\Metric\Network;

trait InteractsWithMetric
{
    public function registerMetrics(): void
    {
        if (method_exists($this, 'addMetric')) {
            $this->addMetric(new Http);
            $this->addMetric(new Framework);
            $this->addMetric(new Application);
            $this->addMetric(new Network);
        }
    }
}
