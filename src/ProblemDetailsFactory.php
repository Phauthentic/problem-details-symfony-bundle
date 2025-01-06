<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent as KernelExceptionEvent;

/**
 *
 */
readonly class ProblemDetailsFactory implements ProblemDetailsFactoryInterface, FromExceptionEventFactoryInterface
{
    public function __construct(
        private string $defaultType = 'about:blank',
        private string $defaultTitle = 'Validation Failed',
        private int $defaultStatus = Response::HTTP_UNPROCESSABLE_ENTITY,
        private string $errorField = 'errors'
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
        array $extensions = []
    ): Response {
        return ProblemDetailsResponse::create(
            status: $status,
            type: $type,
            title: $title,
            instance: $instance,
            extensions: $extensions
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
            status: $this->defaultStatus,
            type: $this->defaultType,
            title: $this->defaultTitle,
            instance: $event->getRequest()->getRequestUri(),
            extensions: [
                $this->errorField => $errors,
            ]
        );
    }
}
