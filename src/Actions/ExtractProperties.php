<?php

namespace Nadi\Drupal\Actions;

use ReflectionClass;

class ExtractProperties
{
    public static function from($target): array
    {
        $properties = [];

        foreach ((new ReflectionClass($target))->getProperties() as $property) {
            $property->setAccessible(true);

            if (PHP_VERSION_ID >= 70400 && ! $property->isInitialized($target)) {
                continue;
            }

            $value = $property->getValue($target);

            if (is_object($value)) {
                $properties[$property->getName()] = [
                    'class' => get_class($value),
                    'properties' => json_decode(json_encode($value), true),
                ];
            } else {
                $properties[$property->getName()] = json_decode(json_encode($value), true);
            }
        }

        return $properties;
    }
}
