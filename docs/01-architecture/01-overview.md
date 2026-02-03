# Overview

The Nadi Drupal module is a framework-specific adapter that wraps the core `nadi-pro/nadi-php` SDK
and integrates with Drupal's Symfony-based event system. It monitors exceptions, slow database
queries, and HTTP request/response cycles.

## Three-Layer Architecture

```text
Drupal Events (Symfony)
        |
        v
  NadiEventSubscriber  -->  Handler (Exception / Query / HTTP)
        |                          |
        v                          v
   Transporter            Metric Collection
        |                (Framework, Http, Network, Application)
        v
   Driver (Log / HTTP / OpenTelemetry)
```

## Core Flow

1. A Drupal/Symfony event fires (`KernelEvents::EXCEPTION`, `KernelEvents::TERMINATE`, `StatementExecutionEndEvent`)
2. `NadiEventSubscriber` dispatches to the appropriate handler
3. The handler captures event data, collects metrics, and builds OpenTelemetry attributes
4. The sampling strategy decides whether to process the entry
5. An `Entry` object is created with a UUID, type, family hash, and trace context
6. The entry is buffered in the transporter, then flushed via the configured driver on `__destruct`

## Module Integration Points

The module registers three Drupal services in `nadi.services.yml`:

| Service | Class | Purpose |
| ------- | ----- | ------- |
| `nadi` | `Nadi\Drupal\Nadi` | Main orchestrator, injected with `ConfigFactoryInterface` |
| `nadi.event_subscriber` | `NadiEventSubscriber` | Subscribes to kernel and database events |
| `nadi.otel_middleware` | `NadiOpenTelemetryMiddleware` | HTTP middleware for OTel span management (priority 400) |

## Namespace Convention

All classes use `Nadi\Drupal\*` as the root namespace, following the pattern of sibling SDKs
(`Nadi\Symfony\*`, `Nadi\WordPress\*`, `Nadi\Laravel\*`).

## Next Steps

- [Components](02-components.md) for detailed class breakdowns
- [Patterns](03-patterns.md) for design conventions
