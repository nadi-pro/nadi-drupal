# Testing

How to run tests, the test structure, and guidelines for writing new tests.

## Running Tests

```bash
composer test                              # Run all tests
./vendor/bin/phpunit --filter=TestName     # Run a single test
```

## Test Structure

```text
tests/src/
├── TestCase.php                           # Base test class
└── Unit/
    ├── ActionsTest.php                    # ExceptionContext and ExtractProperties
    ├── DataEntryTest.php                  # Entry and ExceptionEntry data classes
    ├── TransporterTest.php                # Transporter configuration and behavior
    └── Handler/
        └── HandleExceptionEventTest.php   # Exception handler
```

All tests extend `Nadi\Drupal\Tests\TestCase`.

## Code Formatting

```bash
composer format    # Run Laravel Pint
```

## Writing New Tests

### Test Location

Place unit tests in `tests/src/Unit/`. Mirror the source directory structure:

- `src/Handler/HandleQueryEvent.php` -> `tests/src/Unit/Handler/HandleQueryEventTest.php`
- `src/Metric/Framework.php` -> `tests/src/Unit/Metric/FrameworkTest.php`

### Test Class Convention

```php
<?php

namespace Nadi\Drupal\Tests\Unit;

use Nadi\Drupal\Tests\TestCase;

class MyNewTest extends TestCase
{
    public function test_it_does_something(): void
    {
        // Arrange
        // Act
        // Assert
    }
}
```

### Testing Handlers

Handlers depend on `Nadi` (which requires `ConfigFactoryInterface`). Mock these dependencies
in your test setup. See `HandleExceptionEventTest.php` for a reference implementation.

## Next Steps

- [Extending](03-extending.md) for adding new functionality
- [Components](../01-architecture/02-components.md) for class details
