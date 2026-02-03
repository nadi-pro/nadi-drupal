<?php

namespace Nadi\Drupal\Tests\Unit;

use Nadi\Drupal\Actions\ExtractProperties;
use Nadi\Drupal\Tests\TestCase;

class ActionsTest extends TestCase
{
    public function test_extract_properties_from_object(): void
    {
        $obj = new class
        {
            public string $name = 'test';

            public int $count = 42;

            protected bool $active = true;
        };

        $properties = ExtractProperties::from($obj);

        $this->assertIsArray($properties);
        $this->assertEquals('test', $properties['name']);
        $this->assertEquals(42, $properties['count']);
        $this->assertTrue($properties['active']);
    }

    public function test_extract_properties_handles_uninitialized(): void
    {
        $obj = new class
        {
            public string $name;

            public int $initialized = 1;
        };

        $properties = ExtractProperties::from($obj);

        $this->assertArrayNotHasKey('name', $properties);
        $this->assertEquals(1, $properties['initialized']);
    }
}
