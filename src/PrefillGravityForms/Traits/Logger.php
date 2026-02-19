<?php

declare(strict_types=1);

namespace OWC\PrefillGravityForms\Traits;

use Exception;
use Monolog\Level;
use function OWC\PrefillGravityForms\Foundation\Helpers\resolve;
use OWC\PrefillGravityForms\GravityForms\GravityFormsSettings;

trait Logger
{
    public function logException(Exception $exception, array $context = []): void
    {
        try {
            $level = Level::from($exception->getCode());
            $method = $level->toPsrLogLevel();
        } catch (Exception $e) {
            $method = 'error';
        }

        /** @var Logger */
        $logger = resolve('logger');

        if (! method_exists($logger, $method)) {
            $method = 'error';
        }

        /**
         * Intercept the exception for further processing, such as logging to e.g. Sentry from the project itself.
         *
         * @param Exception $exception The exception to intercept.
         * @param string $method PSR‑3 log level name (e.g. 'error', 'debug').
         *
         * @since NEXT
         */
        do_action('pg::exception/intercept', $exception, $method);

        if (! GravityFormsSettings::make()->loggingEnabled()) {
            return;
        }

        $logger->$method($exception->getMessage(), $context);
    }
}
