<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent as KernelExceptionEvent;

readonly class ProblemDetailsFactory implements ProblemDetailsFactoryInterface, FromExceptionEventFactoryInterface
{
    public function __construct(
        private string $type = 'about:blank',
        private string $title = 'Validation Failed',
        private int $status = Response::HTTP_UNPROCESSABLE_ENTITY,
    ) {
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(StaticAccess)
     */
    public function createResponse(
        int $status,
        string $type = 'about:blank',
        ?string $title = null,
        ?string $instance = null,
        array $errors = []
    ): Response {
        return ProblemDetailsResponse::create(
            status: $status,
            type: $type,
            title: $title,
            instance: $instance,
            errors: $errors
        );
    }

    /**
     * @param KernelExceptionEvent $event
     * @param array<int, array<string, mixed>> $errors
     * @return Response
     * @SuppressWarnings(StaticAccess)
     */
    public function createResponseFromKernelExceptionEvent(KernelExceptionEvent $event, array $errors): Response
    {
        return ProblemDetailsResponse::create(
            status: $this->status,
            type: $this->type,
            title: $this->title,
            instance: $event->getRequest()->getRequestUri(),
            errors: $errors
        );
    }
}
