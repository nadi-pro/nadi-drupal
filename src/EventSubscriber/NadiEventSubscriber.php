<?php

namespace Nadi\Drupal\EventSubscriber;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Nadi\Drupal\Handler\HandleExceptionEvent;
use Nadi\Drupal\Handler\HandleHttpRequestEvent;
use Nadi\Drupal\Handler\HandleQueryEvent;
use Nadi\Drupal\Nadi;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class NadiEventSubscriber implements EventSubscriberInterface
{
    private bool $enabled;

    private array $config;

    public function __construct(
        private Nadi $nadi,
    ) {
        $this->enabled = $this->nadi->isEnabled();
        $this->config = $this->nadi->getConfig();

        if ($this->enabled) {
            $this->enableDatabaseEvents();
        }
    }

    public static function getSubscribedEvents(): array
    {
        $events = [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
            KernelEvents::TERMINATE => ['onKernelTerminate', 0],
        ];

        if (class_exists(StatementExecutionEndEvent::class)) {
            $events[StatementExecutionEndEvent::class] = ['onStatementExecutionEnd', 0];
        }

        return $events;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (! $this->enabled) {
            return;
        }

        try {
            $handler = new HandleExceptionEvent($this->nadi);
            $handler->handle($event);
        } catch (\Throwable $e) {
            // Silently ignore monitoring errors
        }
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (! $this->enabled) {
            return;
        }

        try {
            $handler = new HandleHttpRequestEvent($this->nadi);
            $handler->setConfig($this->config);
            $handler->handle($event);
        } catch (\Throwable $e) {
            // Silently ignore monitoring errors
        }
    }

    public function onStatementExecutionEnd(StatementExecutionEndEvent $event): void
    {
        if (! $this->enabled) {
            return;
        }

        try {
            $handler = new HandleQueryEvent($this->nadi);
            $handler->setConfig($this->config);
            $handler->handle($event);
        } catch (\Throwable $e) {
            // Silently ignore monitoring errors
        }
    }

    private function enableDatabaseEvents(): void
    {
        try {
            if (class_exists(StatementExecutionEndEvent::class)) {
                Database::getConnection()->enableEvents([StatementExecutionEndEvent::class]);
            }
        } catch (\Throwable $e) {
            // Database may not be available yet
        }
    }
}
