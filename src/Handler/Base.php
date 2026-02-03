<?php

namespace Nadi\Drupal\Handler;

use Nadi\Drupal\Nadi;

class Base
{
    public function __construct(private Nadi $nadi) {}

    public function store(array $data): void
    {
        $this->nadi->store($data);
    }

    public function hash(string $value): string
    {
        return sha1($value);
    }

    protected function getNadi(): Nadi
    {
        return $this->nadi;
    }
}
