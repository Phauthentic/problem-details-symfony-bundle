<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Validation;

use Phauthentic\Symfony\ProblemDetails\FromExceptionEventFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Converts a ValidationFailedException to a ProblemDetails response containing the errors.
 *
 * @link https://www.rfc-editor.org/rfc/rfc9457.html
 * @link https://symfony.com/doc/current/components/http_kernel.html#9-handling-exceptions-the-kernel-exception-event
 * @link https://symfony.com/doc/current/validation.html
 *
 * @SuppressWarnings(LongClassName)
 */
readonly class ValidationErrorsToProblemDetailsKernelEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ValidationErrorsBuilderInterface $validationErrorsBuilder,
        private FromExceptionEventFactoryInterface $problemDetailsResponseFactory
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException'
        ];
    }

    private function isNotAJsonRequest(ExceptionEvent $event): bool
    {
        return $event->getRequest()->getPreferredFormat() !== 'json';
    }

    public function extractValidationFailedException(ExceptionEvent $event): ?ValidationFailedException
    {
        $throwAble = $event->getThrowable();
        if ($throwAble instanceof UnprocessableEntityHttpException) {
            $throwAble = $throwAble->getPrevious();
        }

        if ($throwAble instanceof ValidationFailedException) {
            return $throwAble;
        }

        return null;
    }

    /**
     * Specification:
     * - Checks if the request is not a JSON request.
     *   - If not return.
     * - Checks if the exception is a ValidationFailedException.
     *   - If not return.
     * - Converts a ValidationFailedException to an array of errors.
     * - Creates a ProblemDetails response containing the errors.
     */
    public function onException(ExceptionEvent $event): void
    {
        if ($this->isNotAJsonRequest($event)) {
            return;
        }

        $validationFailedException = $this->extractValidationFailedException($event);
        if (!$validationFailedException) {
            return;
        }

        $errors = $this->validationErrorsBuilder->buildErrors($validationFailedException);
        $response = $this->problemDetailsResponseFactory->createResponseFromKernelExceptionEvent($event, $errors);

        $event->setResponse($response);
    }
}
