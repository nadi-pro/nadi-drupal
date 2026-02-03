<?php

namespace Nadi\Drupal\Metric;

use Nadi\Drupal\Support\OpenTelemetrySemanticConventions;
use Nadi\Metric\Base;

class Framework extends Base
{
    public function metrics(): array
    {
        return [
            'framework.name' => 'drupal',
            'framework.version' => \Drupal::VERSION,
            OpenTelemetrySemanticConventions::SERVICE_NAME => 'drupal-app',
            OpenTelemetrySemanticConventions::SERVICE_VERSION => '1.0.0',
            OpenTelemetrySemanticConventions::DEPLOYMENT_ENVIRONMENT => 'production',
        ];
    }
}
