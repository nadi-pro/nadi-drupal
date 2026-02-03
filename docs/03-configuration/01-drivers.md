# Drivers

The Nadi module supports three transport drivers for sending monitoring data.

## Log Driver

Writes monitoring data to local JSON files. This is the default driver and requires no external services.

| Setting | Default | Description |
| ------- | ------- | ----------- |
| `connections.log.path` | `private://nadi` | Directory where log files are stored |

The directory is created automatically during module installation. Use Drupal's `private://`
stream wrapper for security.

## HTTP Driver

Sends data directly to the Nadi API via HTTP using Guzzle.

| Setting | Default | Description |
| ------- | ------- | ----------- |
| `connections.http.api_key` | (empty) | Sanctum token for API authentication |
| `connections.http.app_key` | (empty) | Application identifier |
| `connections.http.endpoint` | `https://nadi.pro/api` | API endpoint URL |
| `connections.http.version` | `v1` | API version |

Get your API key and Application key from [nadi.pro](https://nadi.pro).

## OpenTelemetry Driver

Exports monitoring data as OpenTelemetry spans via OTLP (HTTP protocol).

| Setting | Default | Description |
| ------- | ------- | ----------- |
| `connections.opentelemetry.endpoint` | `http://localhost:4318` | OTel collector endpoint |
| `connections.opentelemetry.service_name` | `drupal-app` | Service name in traces |
| `connections.opentelemetry.service_version` | `1.0.0` | Service version in traces |
| `connections.opentelemetry.deployment_environment` | `production` | Deployment environment label |
| `connections.opentelemetry.suppress_errors` | `true` | Suppress OTel SDK errors |

When using this driver, the `NadiOpenTelemetryMiddleware` is active. It manages span lifecycle:

1. Extracts W3C Trace Context from incoming request headers
2. Creates a `KIND_SERVER` span with the parent context
3. Sets status code and span status on the response
4. Injects trace context headers into the response

## Driver Resolution

The transporter resolves the driver class dynamically from the config value:

```text
log           -> \Nadi\Transporter\Log
http          -> \Nadi\Transporter\Http
opentelemetry -> \Nadi\Transporter\Opentelemetry
```

The driver must implement `\Nadi\Transporter\Contract`.

## Next Steps

- [Sampling](02-sampling.md) for controlling event volume
- [Reference](03-reference.md) for all configuration keys
