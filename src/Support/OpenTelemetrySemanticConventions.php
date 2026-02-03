<?php

namespace Nadi\Drupal\Support;

use Nadi\Support\OpenTelemetrySemanticConventions as CoreConventions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OpenTelemetrySemanticConventions extends CoreConventions
{
    public const DRUPAL_ROUTE_NAME = 'drupal.route.name';

    public const DRUPAL_CONTROLLER = 'drupal.controller';

    public const DRUPAL_MODULE = 'drupal.module';

    public const DB_CONNECTION_NAME = 'db.connection.name';

    public const HTTP_CLIENT_DURATION = 'http.client.duration';

    public static function httpAttributesFromRequest(Request $request, ?Response $response = null): array
    {
        $attributes = [
            self::HTTP_METHOD => $request->getMethod(),
            self::HTTP_URL => $request->getUri(),
            self::HTTP_SCHEME => $request->getScheme(),
            self::HTTP_HOST => $request->getHost(),
            self::HTTP_TARGET => $request->getRequestUri(),
        ];

        if ($userAgent = $request->headers->get('User-Agent')) {
            $attributes[self::HTTP_USER_AGENT] = $userAgent;
        }

        if ($routeName = $request->attributes->get('_route')) {
            $attributes[self::DRUPAL_ROUTE_NAME] = $routeName;
        }

        if ($controller = $request->attributes->get('_controller')) {
            $attributes[self::DRUPAL_CONTROLLER] = $controller;
        }

        if ($clientIp = $request->getClientIp()) {
            $attributes[self::HTTP_CLIENT_IP] = $clientIp;
        }

        if ($response) {
            $attributes[self::HTTP_STATUS_CODE] = $response->getStatusCode();
        }

        return $attributes;
    }

    public static function httpAttributesFromGlobals(): array
    {
        $attributes = [];

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $attributes[self::HTTP_METHOD] = $_SERVER['REQUEST_METHOD'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
            $attributes[self::HTTP_URL] = $scheme.'://'.$host.$_SERVER['REQUEST_URI'];
            $attributes[self::HTTP_SCHEME] = $scheme;
            $attributes[self::HTTP_HOST] = $host;
            $attributes[self::HTTP_TARGET] = $_SERVER['REQUEST_URI'];
        }

        return $attributes;
    }

    public static function databaseAttributes(string $connectionName, string $query, float $duration): array
    {
        $attributes = [
            self::DB_SYSTEM => 'unknown',
            self::DB_STATEMENT => $query,
            self::DB_QUERY_DURATION => $duration,
        ];

        if (preg_match('/^\s*(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER|TRUNCATE)\s+/i', $query, $matches)) {
            $attributes[self::DB_OPERATION] = strtoupper($matches[1]);
        }

        if (preg_match('/(?:FROM|INTO|UPDATE|TABLE)\s+`?(\w+)`?/i', $query, $matches)) {
            $attributes[self::DB_SQL_TABLE] = $matches[1];
        }

        return $attributes;
    }

    public static function userAttributes(): array
    {
        try {
            $currentUser = \Drupal::currentUser();
            if ($currentUser && $currentUser->isAuthenticated()) {
                return [
                    self::USER_ID => (string) $currentUser->id(),
                    self::USER_NAME => $currentUser->getAccountName() ?? '',
                    self::USER_EMAIL => $currentUser->getEmail() ?? '',
                ];
            }
        } catch (\Throwable $e) {
            // Drupal may not be fully bootstrapped
        }

        return [];
    }

    public static function sessionAttributes(): array
    {
        if (session_status() === PHP_SESSION_ACTIVE && session_id()) {
            return [self::SESSION_ID => session_id()];
        }

        return [];
    }

    public static function exceptionAttributes(\Throwable $exception): array
    {
        return parent::exceptionAttributes($exception);
    }

    public static function performanceAttributes(float $startTime, ?int $memoryPeak = null): array
    {
        return parent::performanceAttributes($startTime, $memoryPeak);
    }
}
