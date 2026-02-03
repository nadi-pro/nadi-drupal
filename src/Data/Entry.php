<?php

namespace Nadi\Drupal\Data;

use Drupal\Core\Session\AccountInterface;
use Nadi\Data\Entry as DataEntry;
use Nadi\Drupal\Concerns\InteractsWithMetric;

class Entry extends DataEntry
{
    use InteractsWithMetric;

    public $user;

    public function __construct($type, array $content, $uuid = null)
    {
        parent::__construct($type, $content, $uuid);
        $this->registerMetrics();
    }

    public function user(AccountInterface $account): static
    {
        $this->user = $account;

        $id = $account->id();
        $name = $account->getAccountName() ?? '';
        $email = $account->getEmail() ?? '';

        $this->content = array_merge($this->content, [
            'user' => [
                'id' => $id,
                'name' => $name,
                'email' => $email,
            ],
        ]);

        $this->tags(['Auth:'.$id]);

        return $this;
    }
}
