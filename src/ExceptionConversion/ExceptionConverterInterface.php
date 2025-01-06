<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\ExceptionConversion;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Throwable;

/**
 * Handles Thowable and converts them into a Problem Details HTTP response.
 *
 * Notice that you might need to adjust the priority of the listener in your services.yaml file to make sure it is
 * executed in the right order if you have other listeners.
 */
interface ExceptionConverterInterface
{
    public function canHandle(Throwable $throwable): bool;

    public function convertExceptionToErrorDetails(Throwable $throwable, ExceptionEvent $event): Response;
}
