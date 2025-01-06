<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\ExceptionConversion;

use Phauthentic\Symfony\ProblemDetails\ProblemDetailsFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Handles Symfony\Component\HttpKernel\Exception\HttpException exceptions and converts them into Problem Details HTTP
 * responses.
 *
 * Notice that you might need to adjust the priority of the order in your services.yaml file to make sure it is
 * executed in the right order if you have other converters.
 *
 * @link https://www.rfc-editor.org/rfc/rfc9457.html
 */
class HttpExceptionConverter implements ExceptionConverterInterface
{
    public function __construct(
        protected ProblemDetailsFactoryInterface $problemDetailsFactory
    ) {
    }

    public function canHandle(Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }

    public function convertExceptionToErrorDetails(Throwable $throwable, ExceptionEvent $event): Response
    {
        /** @var HttpException $throwable */
        return $this->problemDetailsFactory->createResponse(
            status: $throwable->getStatusCode(),
            type: 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/' . $throwable->getStatusCode(),
            title: $throwable->getMessage()
        );
    }
}
