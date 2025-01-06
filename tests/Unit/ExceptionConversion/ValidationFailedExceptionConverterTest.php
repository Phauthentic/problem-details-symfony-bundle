<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit\ExceptionConversion;

use Exception;
use Phauthentic\Symfony\ProblemDetails\ExceptionConversion\ValidationFailedExceptionConverter;
use Phauthentic\Symfony\ProblemDetails\FromExceptionEventFactoryInterface;
use Phauthentic\Symfony\ProblemDetails\ProblemDetailsFactory;
use Phauthentic\Symfony\ProblemDetails\Validation\ValidationErrorsBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 *
 */
class ValidationFailedExceptionConverterTest extends TestCase
{
    protected ValidationFailedExceptionConverter $converter;

    public function setUp(): void
    {
        parent::setUp();

        $this->converter = new ValidationFailedExceptionConverter(
            validationErrorsBuilder: new ValidationErrorsBuilder(),
            problemDetailsResponseFactory: new ProblemDetailsFactory()
        );
    }

    #[Test]
    public function testConvertExceptionToErrorDetails(): void
    {
        // Arrange
        $violations = $this->getConstraintViolationList();
        $exception = new ValidationFailedException('Validation failed', $violations);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/profile/1']);
        $request->headers->add(['Accept' => 'application/json']);

        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        // Act
        $response = $this->converter->convertExceptionToErrorDetails($exception, $event);

        // Assert
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

    #[Test]
    public function testCanHandle(): void
    {
        $this->assertFalse($this->converter->canHandle(
            new Exception('Some other exception')
        ));

        $this->assertTrue($this->converter->canHandle(
            new ValidationFailedException(
                'Validation failed',
                $this->getConstraintViolationList()
            )
        ));
    }

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
    public function testExtractValidationFailedExceptionThrowsRuntimeException(): void
    {
        // Arrange
        $exception = new UnprocessableEntityHttpException('Validation failed', new Exception(), 0, []);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/profile/1']);
        $request->headers->add(['Accept' => 'application/json']);
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ValidationFailedException not found');

        // Act
        $this->converter->convertExceptionToErrorDetails($exception, $event);
    }
}
