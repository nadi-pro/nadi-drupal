<?php

namespace Nadi\Drupal\Data;

use Nadi\Data\ExceptionEntry as DataExceptionEntry;
use Nadi\Drupal\Concerns\InteractsWithMetric;

class ExceptionEntry extends DataExceptionEntry
{
    use InteractsWithMetric;

    public function __construct($exception, $type, array $content)
    {
        parent::__construct($exception, $type, $content);
        $this->registerMetrics();
    }
}
