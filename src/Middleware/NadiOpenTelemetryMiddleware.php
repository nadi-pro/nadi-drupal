<?php

namespace Nadi\Drupal\Middleware;

use Nadi\Drupal\Nadi;
use Nadi\Drupal\Support\OpenTelemetrySemanticConventions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class NadiOpenTelemetryMiddleware implements HttpKernelInterface
{
    private $span = null;

    private $scope = null;

    public function __construct(
        private HttpKernelInterface $httpKernel,
        private Nadi $nadi,
    ) {}

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        $config = $this->nadi->getConfig();
        $driver = $config['driver'] ?? 'log';

        if ($driver !== 'opentelemetry' || $type !== self::MAIN_REQUEST) {
            return $this->httpKernel->handle($request, $type, $catch);
        }

        try {
            $this->startSpan($request);
        } catch (\Throwable $e) {
            // Silently ignore
        }

        try {
            $response = $this->httpKernel->handle($request, $type, $catch);
        } catch (\Throwable $e) {
            $this->recordException($e);

            throw $e;
        }

        try {
            $this->endSpan($response);
        } catch (\Throwable $e) {
            // Silently ignore
        }

        return $response;
    }

    private function startSpan(Request $request): void
    {
        if (! class_exists(\OpenTelemetry\API\Globals::class)) {
            return;
        }

        $carrier = [];
        foreach ($request->headers->all() as $name => $values) {
            $carrier[strtolower($name)] = $values[0] ?? '';
        }

        $context = \OpenTelemetry\API\Trace\Propagation\TraceContextPropagator::getInstance()->extract($carrier);
        $spanName = $request->getMethod().' '.$request->getPathInfo();

        $tracer = \OpenTelemetry\API\Globals::tracerProvider()->getTracer('nadi-drupal');
        $this->span = $tracer->spanBuilder($spanName)
            ->setSpanKind(\OpenTelemetry\API\Trace\SpanKind::KIND_SERVER)
            ->setParent($context)
            ->startSpan();

        $this->scope = $this->span->activate();
    }

    private function endSpan(Response $response): void
    {
        if (! $this->span) {
            return;
        }

        $statusCode = $response->getStatusCode();
        $this->span->setAttribute(OpenTelemetrySemanticConventions::HTTP_STATUS_CODE, $statusCode);

        if ($statusCode >= 400) {
            $this->span->setStatus(\OpenTelemetry\API\Trace\StatusCode::STATUS_ERROR, "HTTP {$statusCode}");
        } else {
            $this->span->setStatus(\OpenTelemetry\API\Trace\StatusCode::STATUS_OK);
        }

        $carrier = [];
        \OpenTelemetry\API\Trace\Propagation\TraceContextPropagator::getInstance()->inject($carrier, null, \OpenTelemetry\Context\Context::getCurrent());
        foreach ($carrier as $name => $value) {
            $response->headers->set($name, $value);
        }

        $this->span->end();
        $this->scope?->detach();
    }

    private function recordException(\Throwable $exception): void
    {
        if (! $this->span) {
            return;
        }

        try {
            $this->span->recordException($exception);
            $this->span->setStatus(\OpenTelemetry\API\Trace\StatusCode::STATUS_ERROR, $exception->getMessage());
            $this->span->end();
            $this->scope?->detach();
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }
}
