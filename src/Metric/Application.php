<?php

namespace Nadi\Drupal\Metric;

use Nadi\Metric\Base;

class Application extends Base
{
    public function metrics(): array
    {
        $metrics = [];

        $metrics['app.environment'] = 'production';

        if (PHP_SAPI === 'cli') {
            $metrics['app.context'] = 'drush';
            if (isset($_SERVER['argv'])) {
                $metrics['app.command'] = implode(' ', array_slice($_SERVER['argv'], 1));
            }
        }

        try {
            $siteName = \Drupal::config('system.site')->get('name');
            if ($siteName) {
                $metrics['app.site_name'] = $siteName;
            }
        } catch (\Throwable $e) {
            // Config may not be available yet
        }

        return $metrics;
    }
}
