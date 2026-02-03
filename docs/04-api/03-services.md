# Services

The Nadi module registers services in Drupal's dependency injection container. You can use these services from custom modules.

## Registered Services

Defined in `nadi.services.yml`:

```yaml
services:
  nadi:
    class: Nadi\Drupal\Nadi
    arguments:
      - '@config.factory'

  nadi.event_subscriber:
    class: Nadi\Drupal\EventSubscriber\NadiEventSubscriber
    arguments:
      - '@nadi'
    tags:
      - { name: event_subscriber }

  nadi.otel_middleware:
    class: Nadi\Drupal\Middleware\NadiOpenTelemetryMiddleware
    arguments:
      - '@nadi'
    tags:
      - { name: http_middleware, priority: 400 }
```

## Using the Nadi Service

### From Procedural Code

```php
/** @var \Nadi\Drupal\Nadi $nadi */
$nadi = \Drupal::service('nadi');
```

### Via Dependency Injection

```php
use Nadi\Drupal\Nadi;

class MyService
{
    public function __construct(
        private Nadi $nadi,
    ) {}
}
```

Register in your module's `*.services.yml`:

```yaml
services:
  my_module.my_service:
    class: Drupal\my_module\MyService
    arguments:
      - '@nadi'
```

## Available Methods

```php
$nadi->isEnabled();                    // bool - Check if monitoring is active
$nadi->getConfig();                    // array - Get full configuration
$nadi->store(array $data);             // void - Buffer an entry for sending
$nadi->recordException(\Throwable $e); // void - Record an exception entry
$nadi->recordQuery($sql, $duration);   // void - Record a query entry
$nadi->send();                         // void - Flush all buffered entries
$nadi->test();                         // mixed - Test the connection
$nadi->verify();                       // mixed - Verify configuration
$nadi->getTransporter();               // Transporter - Access the transporter
```

## Recording Custom Events

```php
use Nadi\Data\Type;
use Nadi\Drupal\Data\Entry;

$nadi = \Drupal::service('nadi');

$entry = Entry::make(Type::CUSTOM, [
    'key' => 'value',
    'description' => 'Something happened',
]);

$nadi->store($entry->toArray());
```

## Next Steps

- [Drush Commands](01-drush-commands.md) for CLI usage
- [Extending](../02-development/03-extending.md) for adding event handlers
