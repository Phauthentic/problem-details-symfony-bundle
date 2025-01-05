<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use Symfony\Component\HttpFoundation\JsonResponse;
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
class ThrowableToProblemDetailsKernelListener
{
    /**
     * @param string $environment
     * @param array<callable> $mappers
     */
    public function __construct(
        protected string $environment = 'prod',
        protected array $mappers = []
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        $class = get_class($throwable);
        if (isset($this->mappers[$class])) {
            $mapper = $this->mappers[$class];
            $response = $mapper($throwable);

            $event->setResponse($response);

            return;
        }

        $event->setResponse($this->buildResponse($throwable));
    }

    private function buildResponse(Throwable $throwable): JsonResponse
    {
        $data = [
            'type' => 'about:blank',
            'title' => $throwable->getMessage(),
            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ];

        if ($this->environment === 'dev') {
            $data['trace'] = $throwable->getTrace();
        }

        return new JsonResponse(
            data: $data,
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            headers: [
                'Content-Type' => 'application/problem+json',
            ]
        );
    }
}
