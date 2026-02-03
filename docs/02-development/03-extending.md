# Extending

How to add new event handlers, metrics, and monitoring capabilities.

## Adding a New Event Handler

### Step 1: Create the Handler Class

Create a new class in `src/Handler/` extending `Handler\Base`:

```php
<?php

namespace Nadi\Drupal\Handler;

use Nadi\Data\Type;
use Nadi\Drupal\Data\Entry;

class HandleCustomEvent extends Base
{
    private array $config = [];

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function handle($event): void
    {
        $entryData = [
            'key' => 'value',
            // Capture event-specific data
        ];

        $this->store(
            Entry::make(Type::CUSTOM, $entryData)
                ->setHashFamily($this->hash('unique_key' . date('Y-m-d')))
                ->tags(['custom', 'tag:value'])
                ->toArray()
        );
    }
}
```

### Step 2: Register in the Event Subscriber

Add the event subscription in `NadiEventSubscriber::getSubscribedEvents()`:

```php
public static function getSubscribedEvents(): array
{
    $events = [
        KernelEvents::EXCEPTION => ['onKernelException', 0],
        KernelEvents::TERMINATE => ['onKernelTerminate', 0],
        'custom.event.name' => ['onCustomEvent', 0],
    ];

    // ...
    return $events;
}
```

### Step 3: Add the Listener Method

Add a handler method in `NadiEventSubscriber`:

```php
public function onCustomEvent($event): void
{
    if (! $this->enabled) {
        return;
    }

    try {
        $handler = new HandleCustomEvent($this->nadi);
        $handler->setConfig($this->config);
        $handler->handle($event);
    } catch (\Throwable $e) {
        // Silently ignore monitoring errors
    }
}
```

### Step 4: Add Tests

Create tests in `tests/src/Unit/Handler/HandleCustomEventTest.php`.

## Key Conventions

- **Hash daily**: Use `$this->hash(unique_key . date('Y-m-d'))` to group similar entries by day
- **Silent failures**: Always wrap handler calls in `try-catch(\Throwable)`
- **OTel attributes**: Use `OpenTelemetrySemanticConventions` for attribute naming
- **Metrics**: Entries created via `Entry::make()` automatically register all four metric classes

## Adding a New Metric

Create a class in `src/Metric/` extending `Nadi\Metric\Base`:

```php
<?php

namespace Nadi\Drupal\Metric;

use Nadi\Metric\Base;

class Custom extends Base
{
    public function metrics(): array
    {
        return [
            'custom.key' => 'value',
        ];
    }
}
```

Register it in the `InteractsWithMetric` trait if it should apply to all entries.

## Next Steps

- [Architecture Patterns](../01-architecture/03-patterns.md) for design conventions
- [Testing](02-testing.md) for writing tests
