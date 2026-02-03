<?php

namespace Nadi\Drupal\Metric;

use Nadi\Drupal\Support\OpenTelemetrySemanticConventions;
use Nadi\Metric\Base;

class Http extends Base
{
    public function metrics(): array
    {
        if (PHP_SAPI === 'cli' || ! isset($_SERVER['REQUEST_URI'])) {
            return [];
        }

        return OpenTelemetrySemanticConventions::httpAttributesFromGlobals();
    }
}
