# Components

Every class in the module and its role.

## Nadi (Orchestrator)

`src/Nadi.php` is the main entry point. It reads configuration from Drupal's `ConfigFactory`,
creates a `Transporter` instance, and provides methods to store entries, record exceptions,
and record queries.

```php
use Nadi\Drupal\Nadi;

// Injected via Drupal service container
$nadi = \Drupal::service('nadi');
$nadi->isEnabled();   // Check master toggle
$nadi->store($data);  // Buffer an entry
$nadi->send();        // Flush buffered entries
$nadi->test();        // Test the connection
$nadi->verify();      // Verify configuration
```

## Transporter

`src/Transporter.php` wraps the core SDK's transport and sampling layers. On construction it:

1. Resolves the driver class from config (`\Nadi\Transporter\Log`, `\Nadi\Transporter\Http`,
   `\Nadi\Transporter\Opentelemetry`)
2. Configures the driver with connection-specific settings
3. Creates a `SamplingManager` with the configured strategy
4. Builds a `Service` instance that handles buffering and flushing

The transporter flushes all buffered entries in its `__destruct` method.

## Event Subscriber

`src/EventSubscriber/NadiEventSubscriber.php` implements Symfony's `EventSubscriberInterface`. It subscribes to:

| Event | Method | Handler |
| ----- | ------ | ------- |
| `KernelEvents::EXCEPTION` | `onKernelException` | `HandleExceptionEvent` |
| `KernelEvents::TERMINATE` | `onKernelTerminate` | `HandleHttpRequestEvent` |
| `StatementExecutionEndEvent` | `onStatementExecutionEnd` | `HandleQueryEvent` |

Database events are enabled via `Database::getConnection()->enableEvents()` during construction
when monitoring is active. The `StatementExecutionEndEvent` subscription is conditional on the
class existing (Drupal 10.1+).

## Handlers

All handlers extend `Handler\Base`, which provides `store()` and `hash()` methods.

### HandleExceptionEvent

`src/Handler/HandleExceptionEvent.php` captures unhandled exceptions from `KernelEvents::EXCEPTION`:

- Extracts class, file, line, message, and stack trace
- Gets source code context (~20 lines) via `ExceptionContext`
- Collects OTel attributes (exception, user, session, HTTP)
- Creates an `ExceptionEntry` with daily hash: `sha1(class + file + line + message + date)`
- Tags with exception type and error type

### HandleQueryEvent

`src/Handler/HandleQueryEvent.php` monitors slow database queries via `StatementExecutionEndEvent`:

- Compares execution time against the configured slow threshold
- Skips queries below the threshold
- Extracts SQL, duration, and connection key
- Uses `FetchesStackTrace` to find the originating application file (excluding `vendor/` and `core/`)
- Creates an `Entry` with daily hash: `sha1(sql + date)`
- Tags with `slow`, connection name, and `query.slow:true`

### HandleHttpRequestEvent

`src/Handler/HandleHttpRequestEvent.php` captures HTTP request/response cycles at `KernelEvents::TERMINATE`:

- Checks if the status code falls in the ignored range
- Masks sensitive headers and parameters per configuration
- Captures URI, method, status code, duration, memory usage, route name, and controller
- Creates an `Entry` with hourly hash: `sha1(method + statusCode + uri + date + hour)`
- Tags with HTTP method and status code

## Data Classes

### Entry

`src/Data/Entry.php` extends the core SDK's `Nadi\Data\Entry` with:

- `InteractsWithMetric` trait to register all four metric classes on construction
- `user(AccountInterface)` method to attach Drupal user context (id, name, email)

### ExceptionEntry

`src/Data/ExceptionEntry.php` extends `Nadi\Data\ExceptionEntry` with `InteractsWithMetric`.

## Metrics

All metrics extend `Nadi\Metric\Base` from the core SDK.

| Class | File | Data Collected |
| ----- | ---- | -------------- |
| `Framework` | `src/Metric/Framework.php` | `framework.name` (drupal), `framework.version`, OTel service attributes |
| `Application` | `src/Metric/Application.php` | `app.environment`, `app.context` (drush if CLI), `app.command`, `app.site_name` |
| `Http` | `src/Metric/Http.php` | HTTP attributes from `$_SERVER` globals |
| `Network` | `src/Metric/Network.php` | Host, port, protocol information |

## Middleware

`src/Middleware/NadiOpenTelemetryMiddleware.php` implements `HttpKernelInterface`. When the
driver is `opentelemetry`, it:

1. Extracts trace context from incoming request headers
2. Starts an OTel span (`KIND_SERVER`) with the parent context
3. Delegates to the inner kernel
4. Sets HTTP status code and span status on the response
5. Injects trace context headers into the response
6. Ends the span and detaches the scope

The middleware is only active for `MAIN_REQUEST` when the driver is `opentelemetry`.

## Shipper

`src/Shipper/Shipper.php` manages a Go binary for batched async log shipping. It delegates to
the core SDK's `BinaryManager` for install, update, send, test, and verify operations.

## Actions

### ExceptionContext

`src/Actions/ExceptionContext.php` extracts source code context around an exception:

- Returns ~20 lines of source code centered on the error line
- Handles `eval()`'d code as a special case

### ExtractProperties

`src/Actions/ExtractProperties.php` provides property extraction utilities.

## Support

### OpenTelemetrySemanticConventions

`src/Support/OpenTelemetrySemanticConventions.php` defines standard OTel attribute names and
provides helper methods to build attribute arrays for exceptions, HTTP, database, user, session,
and performance contexts.

## Next Steps

- [Patterns](03-patterns.md) for design conventions
- [Getting Started](../02-development/01-getting-started.md) for setup
