<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit\ExceptionConversion;

use Exception;
use Phauthentic\Symfony\ProblemDetails\ExceptionConversion\GenericThrowableConverter;
use Phauthentic\Symfony\ProblemDetails\ExceptionConversion\HttpExceptionConverter;
use Phauthentic\Symfony\ProblemDetails\ProblemDetailsFactory;
use Phauthentic\Symfony\ProblemDetails\ProblemDetailsResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *
 */
class GenericThrowableConverterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->converter = new GenericThrowableConverter(
            problemDetailsFactory: new ProblemDetailsFactory()
        );
    }

    public function testConvertExceptionToErrorDetails(): void
    {
        $this->assertTrue($this->converter->canHandle(new Exception()));
    }

    public function testConvert(): void
    {
        // Arrange
        $exception = new Exception('Some error');
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        // Act
        $response = $this->converter->convertExceptionToErrorDetails($exception, $event);

        // Assert
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals([
            'status' => 500,
            'type' => 'about:blank',
            'title' => 'Some error',
        ], $data);
    }
}
