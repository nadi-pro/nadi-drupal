<?php

namespace Nadi\Drupal\Tests\Unit;

use Nadi\Data\Type;
use Nadi\Drupal\Data\Entry;
use Nadi\Drupal\Tests\TestCase;

class DataEntryTest extends TestCase
{
    public function test_entry_can_be_created(): void
    {
        $entry = Entry::make(Type::HTTP, [
            'title' => 'Test request',
            'description' => 'Test description',
            'uri' => 'https://example.com',
            'method' => 'GET',
            'response_status' => 200,
        ]);

        $this->assertInstanceOf(Entry::class, $entry);
    }

    public function test_entry_to_array_contains_content(): void
    {
        $entry = Entry::make(Type::QUERY, [
            'sql' => 'SELECT * FROM users',
            'duration' => 150,
        ]);

        $array = $entry->toArray();
        $this->assertArrayHasKey('content', $array);
        $this->assertEquals('SELECT * FROM users', $array['content']['sql']);
    }

    public function test_entry_hash_family(): void
    {
        $entry = Entry::make(Type::QUERY, [
            'sql' => 'SELECT 1',
            'duration' => 100,
        ]);
        $entry->setHashFamily('test-family-hash');

        $array = $entry->toArray();
        $this->assertArrayHasKey('hash_family', $array);
        $this->assertEquals('test-family-hash', $array['hash_family']);
    }

    public function test_entry_tags(): void
    {
        $entry = Entry::make(Type::QUERY, [
            'sql' => 'SELECT 1',
            'duration' => 100,
        ]);
        $entry->tags(['slow', 'query.slow:true']);

        $this->assertTrue($entry->hasMonitoredTag());
    }
}
