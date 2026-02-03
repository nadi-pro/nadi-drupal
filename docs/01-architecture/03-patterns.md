# Patterns

Design patterns and conventions used throughout the Nadi Drupal module.

## Silent Failures

All handler invocations in `NadiEventSubscriber` are wrapped in `try-catch(\Throwable)`.
Monitoring must never break the application:

```php
try {
    $handler = new HandleExceptionEvent($this->nadi);
    $handler->handle($event);
} catch (\Throwable $e) {
    // Silently ignore monitoring errors
}
```

This pattern applies to the event subscriber, middleware, database event enabling, and metric collection.

## Entry Hashing

Each entry gets a family hash that groups similar issues together. The hash formula varies by entry type:

| Type | Hash Input | Grouping Period |
| ---- | ---------- | --------------- |
| Exception | `class + file + line + message + date` | Daily |
| Query | `sql + date` | Daily |
| HTTP | `method + statusCode + uri + date + hour` | Hourly |

The hash is computed with `sha1()` via `Handler\Base::hash()`.

## Stack Trace Filtering

The `FetchesStackTrace` trait walks `debug_backtrace()` and skips frames from `vendor/` and
`core/` directories to find the application-level caller:

```php
protected function ignoredPaths(): array
{
    return [
        DIRECTORY_SEPARATOR.'vendor',
        DIRECTORY_SEPARATOR.'core',
    ];
}
```

Used by `HandleQueryEvent` to identify which application file triggered a slow query.

## Metric Traits

The `InteractsWithMetric` trait registers four metric classes on every entry:

```php
trait InteractsWithMetric
{
    public function registerMetrics(): void
    {
        if (method_exists($this, 'addMetric')) {
            $this->addMetric(new Http);
            $this->addMetric(new Framework);
            $this->addMetric(new Application);
            $this->addMetric(new Network);
        }
    }
}
```

Both `Entry` and `ExceptionEntry` use this trait and call `registerMetrics()` in their constructors.

## Handler Base Class

All event handlers extend `Handler\Base`, which provides:

- `store(array $data)` - delegates to `Nadi::store()`
- `hash(string $value)` - computes `sha1()` hash
- `getNadi()` - access to the main orchestrator

Handlers that need configuration (query threshold, hidden headers) receive it via `setConfig()`.

## OpenTelemetry Semantic Conventions

All OTel attribute names follow the standard naming from `OpenTelemetrySemanticConventions`.
This class provides static helper methods to build attribute arrays, ensuring consistent naming
across all entry types.

## Conditional Event Subscription

The `StatementExecutionEndEvent` subscription is conditional on the class existing, maintaining
compatibility with Drupal versions before 10.1:

```php
if (class_exists(StatementExecutionEndEvent::class)) {
    $events[StatementExecutionEndEvent::class] = ['onStatementExecutionEnd', 0];
}
```

## Next Steps

- [Components](02-components.md) for class details
- [Extending the Module](../02-development/03-extending.md) for adding new handlers
