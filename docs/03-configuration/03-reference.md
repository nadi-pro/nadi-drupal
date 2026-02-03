# Configuration Reference

Complete reference of all configuration keys, defaults, and schema types. Configuration is
stored in Drupal's config system under `nadi.settings`.

## Install Defaults

Default values are defined in `config/install/nadi.settings.yml` and applied when the module is first enabled.

## Full Configuration Tree

```yaml
enabled: true
driver: log

connections:
  log:
    path: 'private://nadi'
  http:
    api_key: ''
    app_key: ''
    endpoint: 'https://nadi.pro/api'
    version: v1
  opentelemetry:
    endpoint: 'http://localhost:4318'
    service_name: 'drupal-app'
    service_version: '1.0.0'
    deployment_environment: 'production'
    suppress_errors: true

query:
  slow_threshold: 500

http:
  hidden_request_headers:
    - authorization
    - php-auth-pw
    - cookie
  hidden_parameters:
    - password
    - password_confirmation
    - pass
  ignored_status_codes:
    - '200-307'

sampling:
  strategy: fixed_rate
  config:
    sampling_rate: 0.1
    base_rate: 0.05
    load_factor: 1.0
    interval_seconds: 60
```

## Key Reference

### General

| Key | Type | Default | Description |
| --- | ---- | ------- | ----------- |
| `enabled` | boolean | `true` | Master toggle for all monitoring |
| `driver` | string | `log` | Transport driver: `log`, `http`, or `opentelemetry` |

### Log Driver

| Key | Type | Default | Description |
| --- | ---- | ------- | ----------- |
| `connections.log.path` | string | `private://nadi` | Log file directory path |

### HTTP Driver

| Key | Type | Default | Description |
| --- | ---- | ------- | ----------- |
| `connections.http.api_key` | string | (empty) | Sanctum API token |
| `connections.http.app_key` | string | (empty) | Application identifier |
| `connections.http.endpoint` | string | `https://nadi.pro/api` | API endpoint URL |
| `connections.http.version` | string | `v1` | API version |

### OpenTelemetry Driver

| Key | Type | Default | Description |
| --- | ---- | ------- | ----------- |
| `connections.opentelemetry.endpoint` | string | `http://localhost:4318` | OTel collector endpoint |
| `connections.opentelemetry.service_name` | string | `drupal-app` | Service name |
| `connections.opentelemetry.service_version` | string | `1.0.0` | Service version |
| `connections.opentelemetry.deployment_environment` | string | `production` | Environment label |
| `connections.opentelemetry.suppress_errors` | boolean | `true` | Suppress OTel SDK errors |

### Query Monitoring

| Key | Type | Default | Description |
| --- | ---- | ------- | ----------- |
| `query.slow_threshold` | integer | `500` | Slow query threshold in milliseconds |

### HTTP Monitoring

| Key | Type | Default | Description |
| --- | ---- | ------- | ----------- |
| `http.hidden_request_headers` | sequence | `[authorization, php-auth-pw, cookie]` | Headers to mask in recorded data |
| `http.hidden_parameters` | sequence | `[password, password_confirmation, pass]` | Parameters to mask in recorded data |
| `http.ignored_status_codes` | sequence | `[200-307]` | Status codes/ranges to skip recording |

Status code ranges use dash notation (e.g., `200-307` matches 200 through 307 inclusive).

### Sampling

| Key | Type | Default | Description |
| --- | ---- | ------- | ----------- |
| `sampling.strategy` | string | `fixed_rate` | Sampling strategy name |
| `sampling.config.sampling_rate` | float | `0.1` | Fixed rate (0.0 to 1.0) |
| `sampling.config.base_rate` | float | `0.05` | Base rate for dynamic strategy |
| `sampling.config.load_factor` | float | `1.0` | Load multiplier for dynamic strategy |
| `sampling.config.interval_seconds` | float | `60` | Interval for interval-based sampling |

## Schema

The configuration schema is defined in `config/schema/nadi.schema.yml` and validates all
settings during Drupal config import/export.

## Next Steps

- [Drivers](01-drivers.md) for driver-specific setup
- [Sampling](02-sampling.md) for strategy details
