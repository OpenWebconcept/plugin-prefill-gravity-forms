<?php

namespace OWC\PrefillGravityForms\Foundation;

use Psr\Log\LoggerInterface;

class TeamsLogger
{
    protected LoggerInterface $teams;

    protected string $name;

    private function __construct(LoggerInterface $teams)
    {
        $this->teams = $teams;
        $this->name = 'Yard | BRP Prefill GravityForms';
    }

    /**
     * Static constructor.
     */
    public static function make(LoggerInterface $teams): self
    {
        return new static($teams);
    }

    /**
     * Add a log record and send to Teams.
     */
    public function addRecord(string $method = 'info', string $title, array $context): void
    {
        if (! $this->isValid($method) || ! method_exists($this->teams, 'withName')) {
            return;
        }

        $this->teams->withName($this->name)->$method($title, $context);
    }

    protected function isValid(string $method): bool
    {
        return method_exists('\Monolog\Logger', $method);
    }
}
