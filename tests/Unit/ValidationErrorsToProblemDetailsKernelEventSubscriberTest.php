<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit;

use JsonException;
use Phauthentic\Symfony\ProblemDetails\ProblemDetailsFactory;
use Phauthentic\Symfony\ProblemDetails\Validation\ValidationErrorsBuilder;
use Phauthentic\Symfony\ProblemDetails\Validation\ValidationErrorsToProblemDetailsKernelEventSubscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;

class ValidationErrorsToProblemDetailsKernelEventSubscriberTest extends TestCase
{
    /**
     * @throws Exception
     * @throws JsonException
     */
    #[Test]
    public function testOnException(): void
    {
        // Arrange
        $violations = $this->getConstraintViolationList();
        $exception = new ValidationFailedException('Validation failed', $violations);

        $listener = $this->getErrorsToProblemDetailsKernelEventSubscriber();

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/profile/1']);
        $request->headers->add(['Accept' => 'application/json']);

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        // Act
        $listener->onException($event);

        // Assert
        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'type' => 'about:blank',
                'title' => 'Validation Failed',
                'status' => 422,
                'instance' => $request->getRequestUri(),
                'errors' => [
                    [
                        'detail' => 'This value is invalid.',
                        'pointer' => '#/field'
                    ],
                    [
                        'detail' => 'This street is invalid.',
                        'pointer' => '#/addresses/1/street'
                    ]
                ]
            ], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }

    /**
     * @return ConstraintViolationList
     * @throws Exception
     */
    private function getConstraintViolationList(): ConstraintViolationList
    {
        $violation1 = $this->createMock(ConstraintViolation::class);
        $violation1->method('getPropertyPath')->willReturn('field');
        $violation1->method('getMessage')->willReturn('This value is invalid.');

        $violation2 = $this->createMock(ConstraintViolation::class);
        $violation2->method('getPropertyPath')->willReturn('addresses[1].street');
        $violation2->method('getMessage')->willReturn('This street is invalid.');

        return new ConstraintViolationList([$violation1, $violation2]);
    }

    #[Test]
    public function testGetSubscribedEvents(): void
    {
        $events = ValidationErrorsToProblemDetailsKernelEventSubscriber::getSubscribedEvents();

        $this->assertEquals([KernelEvents::EXCEPTION => 'onException'], $events);
    }

    #[Test]
    public function testOnExceptionNotValidationFailedException(): void
    {
        // Arrange
        $exception = new \Exception('Some other exception');

        $listener = $this->getErrorsToProblemDetailsKernelEventSubscriber();

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/profile/1']);
        $request->headers->add(['Accept' => 'application/json']);
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        // Act
        $listener->onException($event);

        // Assert
        $response = $event->getResponse();
        $this->assertNull($response);
    }

    /**
     * @return ValidationErrorsToProblemDetailsKernelEventSubscriber
     */
    public function getErrorsToProblemDetailsKernelEventSubscriber(): ValidationErrorsToProblemDetailsKernelEventSubscriber
    {
        $validationErrorsBuilder = new ValidationErrorsBuilder();
        $problemDetailsResponseFactory = new ProblemDetailsFactory();

        return new ValidationErrorsToProblemDetailsKernelEventSubscriber(
            $validationErrorsBuilder,
            $problemDetailsResponseFactory
        );
    }
}
