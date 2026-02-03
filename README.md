# Nadi for Drupal

Nadi monitoring SDK for Drupal ^10.1 and ^11. Monitors exceptions, slow database queries, HTTP errors, and application performance.

## Requirements

- PHP ^8.1
- Drupal ^10.1 || ^11

## Installation

```bash
composer require nadi-pro/nadi-drupal
drush en nadi
```

## Configuration

### Admin UI

Navigate to **Administration > Configuration > System > Nadi Monitoring** (`/admin/config/system/nadi`) to configure:

- **Enable/Disable** monitoring
- **Transport driver** — Log (file-based), HTTP (direct API), or OpenTelemetry
- **API credentials** — API key and Application key for HTTP driver
- **Slow query threshold** — queries exceeding this duration (ms) are recorded
- **Hidden headers/parameters** — sensitive values masked in recorded data
- **Sampling strategy** — control what percentage of events are recorded

### Drivers

| Driver | Description |
|--------|-------------|
| `log` | Writes monitoring data to local JSON files (default: `private://nadi`) |
| `http` | Sends data directly to the Nadi API via HTTP |
| `opentelemetry` | Exports data as OpenTelemetry spans via OTLP |

### Permissions

The `administer nadi` permission controls access to the settings form.

## What Gets Monitored

### Exceptions

All unhandled exceptions are captured with:
- Exception class, file, line, message
- Stack trace
- Source code context (~20 lines around the error)
- OpenTelemetry semantic attributes
- Current user context

### Slow Queries

Database queries exceeding the configured threshold (default: 500ms) via Drupal's `StatementExecutionEndEvent`:
- SQL statement
- Execution duration
- Connection key
- Originating file and line (excluding vendor/core)

### HTTP Requests

Request/response lifecycle captured at kernel termination:
- URI, method, status code
- Request headers (sensitive values masked)
- Request payload (sensitive params masked)
- Duration and memory usage
- Route name and controller

## Drush Commands

```bash
drush nadi:install          # Install shipper binary and show setup instructions
drush nadi:test             # Test the monitoring connection
drush nadi:verify           # Verify configuration is valid
drush nadi:update-shipper   # Update the shipper binary
```

## Development

```bash
composer install
composer test       # Run PHPUnit tests
composer format     # Run Laravel Pint formatter
```

## License

MIT
