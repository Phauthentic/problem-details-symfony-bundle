<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Handles Symfony\Component\HttpKernel\Exception\HttpException exceptions and converts them into Problem Details HTTP
 * responses.
 *
 * Notice that you might need to adjust the priority of the listener in your services.yaml file to make sure it is
 * executed in the right order if you have other listeners.
 *
 * <code>
 * Phauthentic\Symfony\ProblemDetails\HttpExceptionToProblemDetailsKernelListener:
 *      tags:
 *          - { name: kernel.event_listener, event: kernel.exception, priority: -10 }
 * </code>
 *
 * @link https://www.rfc-editor.org/rfc/rfc9457.html
 */
class HttpExceptionToProblemDetailsKernelListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($this->isAHttpException($exception)) {
            /** @var HttpException $exception */
            $event->setResponse($this->buildResponse($exception));
        }
    }

    private function isAHttpException(Throwable $exception): bool
    {
        return $exception instanceof HttpException;
    }

    private function buildResponse(HttpException $httpException): JsonResponse
    {
        return new JsonResponse(
            data: [
                'type' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/' . $httpException->getStatusCode(),
                'title' => $httpException->getMessage(),
                'status' => $httpException->getStatusCode(),
            ],
            status: $httpException->getStatusCode(),
            headers: [
                'Content-Type' => 'application/problem+json',
            ]
        );
    }
}
