<?php

namespace Nadi\Drupal\Handler;

use Nadi\Data\Type;
use Nadi\Drupal\Data\Entry;
use Nadi\Drupal\Support\OpenTelemetrySemanticConventions;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class HandleHttpRequestEvent extends Base
{
    private array $config = [];

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function handle(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $statusCode = $response->getStatusCode();

        $ignoredCodes = $this->config['http']['ignored_status_codes'] ?? [];
        if ($this->isStatusCodeIgnored($statusCode, $ignoredCodes)) {
            return;
        }

        $uri = $request->getUri();
        $method = $request->getMethod();
        $startTime = $request->server->get('REQUEST_TIME_FLOAT', microtime(true));
        $title = "$uri returned HTTP Status Code $statusCode";

        $otelAttributes = OpenTelemetrySemanticConventions::httpAttributesFromRequest($request, $response);
        $userAttributes = OpenTelemetrySemanticConventions::userAttributes();
        $sessionAttributes = OpenTelemetrySemanticConventions::sessionAttributes();
        $performanceAttributes = OpenTelemetrySemanticConventions::performanceAttributes($startTime, memory_get_peak_usage(true));
        $otelData = array_merge($otelAttributes, $userAttributes, $sessionAttributes, $performanceAttributes);

        $headers = [];
        $hiddenHeaders = $this->config['http']['hidden_request_headers'] ?? [];
        foreach ($request->headers->all() as $name => $values) {
            $value = $values[0] ?? '';
            if (in_array(strtolower($name), $hiddenHeaders)) {
                $value = '********';
            }
            $headers[$name] = $value;
        }

        $payload = $request->request->all();
        $hiddenParams = $this->config['http']['hidden_parameters'] ?? [];
        foreach ($hiddenParams as $param) {
            if (isset($payload[$param])) {
                $payload[$param] = '********';
            }
        }

        $routeName = $request->attributes->get('_route', '');
        $controller = $request->attributes->get('_controller', '');

        $entryData = [
            'title' => $title,
            'description' => "$uri for $method request returned HTTP Status Code $statusCode",
            'uri' => $uri,
            'method' => $method,
            'controller_action' => $controller,
            'route_name' => $routeName,
            'headers' => $headers,
            'payload' => $payload,
            'response_status' => $statusCode,
            'duration' => floor((microtime(true) - $startTime) * 1000),
            'memory' => round(memory_get_peak_usage(true) / 1024 / 1025, 1),
            'otel' => $otelData,
        ];

        $this->store(Entry::make(
            Type::HTTP,
            $entryData
        )->setHashFamily(
            $this->hash($method.$statusCode.$uri.date('Y-m-d H'))
        )->tags([
            $method,
            (string) $statusCode,
            'http.method:'.$method,
            'http.status_code:'.$statusCode,
        ])->toArray());
    }

    private function isStatusCodeIgnored(int $statusCode, array $ignoredCodes): bool
    {
        foreach ($ignoredCodes as $code) {
            if (str_contains((string) $code, '-')) {
                [$min, $max] = explode('-', (string) $code);
                if ($statusCode >= (int) $min && $statusCode <= (int) $max) {
                    return true;
                }
            } elseif ((int) $code === $statusCode) {
                return true;
            }
        }

        return false;
    }
}
