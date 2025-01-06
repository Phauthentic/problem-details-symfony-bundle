<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\ExceptionConversion;

use Phauthentic\Symfony\ProblemDetails\FromExceptionEventFactoryInterface;
use Phauthentic\Symfony\ProblemDetails\Validation\ValidationErrorsBuilder;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/**
 * Notice that you might need to adjust the priority of the order in your services.yaml file to make sure it is
 * executed in the right order if you have other converters.
 */
class ValidationFailedExceptionConverter implements ExceptionConverterInterface
{
    public function __construct(
        protected ValidationErrorsBuilder $validationErrorsBuilder,
        protected FromExceptionEventFactoryInterface $problemDetailsResponseFactory
    ) {
    }

    public function canHandle(Throwable $throwable): bool
    {
        if ($throwable instanceof UnprocessableEntityHttpException) {
            $throwable = $throwable->getPrevious();
        }

        return $throwable instanceof ValidationFailedException;
    }

    private function extractValidationFailedException(Throwable $throwable): ValidationFailedException
    {
        if ($throwable instanceof UnprocessableEntityHttpException) {
            $throwable = $throwable->getPrevious();
        }

        if ($throwable instanceof ValidationFailedException) {
            return $throwable;
        }

        throw new RuntimeException('ValidationFailedException not found');
    }

    public function convertExceptionToErrorDetails(Throwable $throwable, ExceptionEvent $event): Response
    {
        $throwable = $this->extractValidationFailedException($throwable);

        return $this->problemDetailsResponseFactory->createResponseFromKernelExceptionEvent(
            $event,
            $this->validationErrorsBuilder->buildErrors($throwable)
        );
    }
}
