# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Nadi monitoring SDK for Drupal. Monitors exceptions, slow queries, HTTP errors, and application performance in Drupal projects. This is a framework-specific adapter that wraps the core `nadi-pro/nadi-php` SDK and integrates with Drupal's event system (Symfony-based).

**Requirements:** PHP ^8.1, Drupal ^10.1 || ^11

## Commands

```bash
composer install          # Install dependencies
composer test             # Run PHPUnit tests
composer format           # Run Laravel Pint code formatting
./vendor/bin/phpunit --filter=TestName  # Run a single test
```

## Architecture

The SDK follows the same three-layer architecture as all Nadi framework SDKs:

```
Drupal Events (Symfony) → Handler Base → Event Handlers → Transporter → Driver (HTTP/Log/OpenTelemetry)
                                              ↓
                                        Metric Collection
                                        (Framework, Http, Network, Application)
```

### Core Flow

1. Drupal/Symfony event fires (KernelEvents::EXCEPTION, KernelEvents::TERMINATE, StatementExecutionEndEvent)
2. NadiEventSubscriber dispatches to framework-specific handler
3. Handler captures event with context, collects metrics via traits
4. Sampling strategy determines whether to process
5. An `Entry` object is created (UUID, type, family hash, trace context)
6. Entry is buffered in the transporter, then flushed via the configured driver

### Namespace Convention

Use `Nadi\Drupal\*` as the root namespace, mirroring sibling SDKs (`Nadi\Symfony\*`, `Nadi\WordPress\*`).

### Key Components

| Component | Purpose |
|-----------|---------|
| `Nadi` | Main orchestrator, accepts ConfigFactoryInterface via DI |
| `Transporter` | Wraps `nadi-pro/nadi-php` core SDK, configures drivers and sampling |
| `Handler\Base` | Base class for all event handlers, provides store() and hash() |
| `Handler\HandleExceptionEvent` | Captures exceptions via Symfony ExceptionEvent |
| `Handler\HandleQueryEvent` | Monitors slow DB queries via StatementExecutionEndEvent (Drupal 10.1+) |
| `Handler\HandleHttpRequestEvent` | Tracks HTTP request/response via Symfony TerminateEvent |
| `EventSubscriber\NadiEventSubscriber` | Subscribes to kernel + database events |
| `Form\NadiSettingsForm` | Admin settings form at /admin/config/system/nadi |
| `Middleware\NadiOpenTelemetryMiddleware` | HTTP middleware for OTel span management |
| `Commands\NadiCommands` | Drush commands: nadi:install, nadi:test, nadi:verify, nadi:update-shipper |
| `Metric\Framework` | Drupal-specific metrics (version, name) |
| `Metric\Application` | App environment, context (cli=drush), site name |
| `Metric\Http` | HTTP attributes from $_SERVER globals |
| `Metric\Network` | Host, port, protocol info |
| `Shipper\Shipper` | Manages Go binary for batched async log shipping |
| `Data\Entry` | Extends core Entry with Drupal user() method |
| `Data\ExceptionEntry` | Extends core ExceptionEntry with metrics |

### Patterns to Follow

- **Traits in `Concerns/`:** `InteractsWithMetric` (registers 4 metric classes), `FetchesStackTrace` (filters vendor/ and core/)
- **All handlers extend** `Handler\Base`
- **All metrics extend** `Nadi\Metric\Base` from the core SDK
- **Entry hashing:** `sha1(class + file + line + message + date)` groups similar issues by day
- **Silent failures:** All handler calls wrapped in try-catch(\Throwable) — monitoring never breaks app

## Configuration

Drupal config stored via ConfigFactory at `nadi.settings`:

| Config Key | Purpose |
|------------|---------|
| `enabled` | Master toggle (boolean) |
| `driver` | Transport type: `log`, `http`, `opentelemetry` |
| `connections.http.api_key` | Sanctum token for authentication |
| `connections.http.app_key` | Application identifier |
| `connections.http.endpoint` | API endpoint URL |
| `connections.log.path` | Log directory (default: `private://nadi`) |
| `query.slow_threshold` | Slow query threshold in milliseconds (default: 500) |
| `sampling.strategy` | Sampling strategy: `fixed_rate`, `dynamic_rate`, `interval`, `peak_load` |

Schema defined in `config/schema/nadi.schema.yml`. Install defaults in `config/install/nadi.settings.yml`.

## File Structure

```
nadi-drupal/
├── composer.json
├── nadi.info.yml             # Module declaration
├── nadi.module               # Module entry point
├── nadi.install              # Install/uninstall hooks + requirements
├── nadi.services.yml         # Service container definitions
├── nadi.routing.yml          # Admin route
├── nadi.links.menu.yml       # Admin menu link
├── nadi.permissions.yml      # Permission definitions
├── drush.services.yml        # Drush command service
├── config/
│   ├── install/nadi.settings.yml
│   └── schema/nadi.schema.yml
├── src/
│   ├── Nadi.php
│   ├── Transporter.php
│   ├── Actions/
│   ├── Commands/
│   ├── Concerns/
│   ├── Data/
│   ├── EventSubscriber/
│   ├── Form/
│   ├── Handler/
│   ├── Metric/
│   ├── Middleware/
│   ├── Shipper/
│   └── Support/
└── tests/src/
    ├── TestCase.php
    └── Unit/
```

## Dependencies

Core SDK dependency: `nadi-pro/nadi-php` ^2.0. This provides:
- `Nadi\Data\Entry` and `Nadi\Data\Type` — data structures
- `Nadi\Transporter\Contract` — transporter interface
- `Nadi\Sampling\Contract` — sampling strategies (FixedRate, Dynamic, Interval, PeakLoad)
- `Nadi\Metric\Base` — base metric class
- `Nadi\Support\OpenTelemetrySemanticConventions` — standard metric naming

Additional shared dependencies: `guzzlehttp/guzzle`, `hisorange/browser-detect`, `open-telemetry/*`.

## Adding a New Event Handler

1. Create a class in `src/Handler/` extending `Handler\Base`
2. Capture the Drupal/Symfony event data and collect metrics
3. Create an `Entry` with the appropriate `Nadi\Data\Type`
4. Hash: `$this->hash(unique_key . date('Y-m-d'))` for daily grouping
5. Register the handler in `NadiEventSubscriber::getSubscribedEvents()`
6. Add tests in `tests/src/Unit/Handler/`
