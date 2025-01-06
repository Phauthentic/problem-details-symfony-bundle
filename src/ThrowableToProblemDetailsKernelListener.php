<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use InvalidArgumentException;
use Phauthentic\Symfony\ProblemDetails\ExceptionConversion\ExceptionConverterInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Handles Thowable and converts it into a Problem Details HTTP response.
 *
 * Notice that you might need to adjust the priority of the converters in your services.yaml file to make sure it is
 * executed in the right order if you have other listeners.
 *
 * <code>
 * Phauthentic\Symfony\ProblemDetails\ThrowableToProblemDetailsKernelListener:
 *      arguments: ['%kernel.environment%', { }]
 *      tags:
 *          - { name: kernel.event_listener, event: kernel.exception, priority: -20 }
 * </code>
 *
 * @link https://www.rfc-editor.org/rfc/rfc9457.html
 */
class ThrowableToProblemDetailsKernelListener
{
    /**
     * @param array<ExceptionConverterInterface> $exceptionConverters
     */
    public function __construct(
        protected array $exceptionConverters = []
    ) {
        if (empty($this->exceptionConverters)) {
            throw new InvalidArgumentException('No exception converter passed!');
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->isNotAJsonRequest($event)) {
            return;
        }

        $this->processConverters($event);
    }

    private function processConverters(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        foreach ($this->exceptionConverters as $exceptionConverter) {
            if (!$exceptionConverter->canHandle($throwable)) {
                continue;
            }

            $response = $exceptionConverter->convertExceptionToErrorDetails($throwable, $event);
            $event->setResponse($response);

            return;
        }
    }

    private function isNotAJsonRequest(ExceptionEvent $event): bool
    {
        return $event->getRequest()->getPreferredFormat() !== 'json';
    }
}
