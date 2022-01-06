<?php

namespace OWC\PrefillGravityForms\Foundation;

use Psr\Log\LoggerInterface;

class TeamsLogger
{
    protected LoggerInterface $instance;

    protected string $name;

    private function __construct(LoggerInterface $instance)
    {
        $this->teams = $instance;
        $this->name = 'Yard | BRP Prefill GravityForms';
    }

    /**
     * Static constructor.
     */
    public static function make(LoggerInterface $instance): self
    {
        return new static($instance);
    }

    /**
     * Add a log record and send to Teams.
     */
    public function addRecord(string $method = 'info', string $title, array $context): void
    {
        if (!$this->isValid($method)) {
            return;
        }

        $this->teams->withName($this->name)->$method($title, $context);
    }

    protected function isValid(string $method): bool
    {
        return method_exists('\Monolog\Logger', $method);
    }
}
