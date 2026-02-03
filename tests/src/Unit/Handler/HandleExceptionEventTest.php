<?php

namespace Nadi\Drupal\Tests\Unit\Handler;

use Nadi\Drupal\Actions\ExceptionContext;
use Nadi\Drupal\Data\ExceptionEntry;
use Nadi\Drupal\Tests\TestCase;

class HandleExceptionEventTest extends TestCase
{
    public function test_exception_context_extracts_file_context(): void
    {
        $exception = new \RuntimeException('Test exception');
        $context = ExceptionContext::get($exception);

        $this->assertIsArray($context);
        $this->assertNotEmpty($context);
    }

    public function test_exception_context_handles_eval_code(): void
    {
        try {
            eval('throw new \RuntimeException("eval error");');
        } catch (\Throwable $e) {
            $context = ExceptionContext::get($e);
            $this->assertIsArray($context);
        }
    }

    public function test_exception_entry_can_be_created(): void
    {
        $exception = new \RuntimeException('Test exception');

        $entry = ExceptionEntry::make(
            $exception,
            \Nadi\Data\Type::EXCEPTION,
            [
                'class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
            ]
        );

        $this->assertInstanceOf(ExceptionEntry::class, $entry);
        $array = $entry->toArray();
        $this->assertArrayHasKey('content', $array);
        $this->assertEquals('Test exception', $array['content']['message']);
    }

    public function test_exception_entry_has_correct_type(): void
    {
        $exception = new \RuntimeException('Test exception');

        $entry = ExceptionEntry::make(
            $exception,
            \Nadi\Data\Type::EXCEPTION,
            [
                'class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
            ]
        );

        $this->assertTrue($entry->isException());
        $array = $entry->toArray();
        $this->assertEquals(\Nadi\Data\Type::EXCEPTION, $array['type']);
    }

    public function test_exception_entry_hash_family_is_set_after_calling_set(): void
    {
        $exception = new \RuntimeException('Test exception');

        $entry = ExceptionEntry::make(
            $exception,
            \Nadi\Data\Type::EXCEPTION,
            [
                'class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
            ]
        );

        // ExceptionEntry::setHashFamily() auto-generates hash from exception details
        $entry->setHashFamily(null);

        $array = $entry->toArray();
        $this->assertNotEmpty($array['hash_family']);
    }
}
