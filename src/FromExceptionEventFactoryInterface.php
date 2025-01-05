<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

interface FromExceptionEventFactoryInterface
{
    /**
     * @param ExceptionEvent $event
     * @param array<int, array<string, mixed>> $errors
     * @return Response
     */
    public function createResponseFromKernelExceptionEvent(ExceptionEvent $event, array $errors): Response;
}
