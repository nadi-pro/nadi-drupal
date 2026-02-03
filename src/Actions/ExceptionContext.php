<?php

namespace Nadi\Drupal\Actions;

use Throwable;

class ExceptionContext
{
    public static function get(Throwable $exception): array
    {
        return static::getEvalContext($exception) ?? static::getFileContext($exception);
    }

    protected static function getEvalContext(Throwable $exception): ?array
    {
        if (str_contains($exception->getFile(), "eval()'d code")) {
            return [$exception->getLine() => "eval()'d code"];
        }

        return null;
    }

    protected static function getFileContext(Throwable $exception): array
    {
        $file = $exception->getFile();
        if (! file_exists($file)) {
            return [];
        }

        $lines = explode("\n", file_get_contents($file));
        $start = max(0, $exception->getLine() - 11);
        $slice = array_slice($lines, $start, 20, true);

        $result = [];
        foreach ($slice as $key => $value) {
            $result[$key + 1] = $value;
        }

        return $result;
    }
}
