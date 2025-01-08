<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\ExceptionConversion;

use Phauthentic\Symfony\ProblemDetails\ProblemDetailsFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Throwable;

/**
 * Handles Thowable and converts it into a Problem Details HTTP response.
 *
 * Notice that you might need to adjust the priority of the listener in your services.yaml file to make sure it is
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
class GenericExceptionConverter implements ExceptionConverterInterface
{
    /**
     * @param ProblemDetailsFactoryInterface $problemDetailsFactory
     * @param string $environment
     * @param array<callable> $mappers
     */
    public function __construct(
        protected ProblemDetailsFactoryInterface $problemDetailsFactory,
        protected string $environment = 'prod',
        protected array $mappers = []
    ) {
    }

    public function canHandle(Throwable $throwable): bool
    {
        return true;
    }

    public function convertExceptionToErrorDetails(Throwable $throwable, ExceptionEvent $event): Response
    {
        $extensions = [];
        if ($this->environment === 'dev' || $this->environment === 'test') {
            $extensions['trace'] = $throwable->getTrace();
        }

        return $this->problemDetailsFactory->createResponse(
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            title: $throwable->getMessage(),
            extensions: $extensions,
        );
    }
}
