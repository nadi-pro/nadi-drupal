<?php

namespace Nadi\Drupal\Handler;

use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Nadi\Data\Type;
use Nadi\Drupal\Concerns\FetchesStackTrace;
use Nadi\Drupal\Data\Entry;
use Nadi\Drupal\Support\OpenTelemetrySemanticConventions;

class HandleQueryEvent extends Base
{
    use FetchesStackTrace;

    private array $config = [];

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function handle(StatementExecutionEndEvent $event): void
    {
        $slowThreshold = $this->config['query']['slow_threshold'] ?? 500;

        $sql = $event->queryString;
        $durationMs = $event->getElapsedTime() * 1000;
        $connectionKey = $event->key;

        if ($durationMs < $slowThreshold) {
            return;
        }

        $otelAttributes = OpenTelemetrySemanticConventions::databaseAttributes($connectionKey, $sql, $durationMs);
        $userAttributes = OpenTelemetrySemanticConventions::userAttributes();
        $sessionAttributes = OpenTelemetrySemanticConventions::sessionAttributes();
        $otelData = array_merge($otelAttributes, $userAttributes, $sessionAttributes);

        $entryData = [
            'connection' => $connectionKey,
            'sql' => $sql,
            'time' => number_format($durationMs, 2, '.', ''),
            'slow' => true,
            'otel' => $otelData,
        ];

        if ($caller = $this->getCallerFromStackTrace()) {
            $otelData[OpenTelemetrySemanticConventions::CODE_FILEPATH] = $caller['file'];
            $otelData[OpenTelemetrySemanticConventions::CODE_LINENO] = $caller['line'];

            $entryData['file'] = $caller['file'];
            $entryData['line'] = $caller['line'];
            $entryData['otel'] = $otelData;
        }

        $this->store(
            Entry::make(Type::QUERY, $entryData)
                ->setHashFamily($this->hash($sql.date('Y-m-d')))
                ->tags([
                    'slow',
                    OpenTelemetrySemanticConventions::DB_CONNECTION_NAME.':'.$connectionKey,
                    'query.slow:true',
                ])
                ->toArray()
        );
    }
}
