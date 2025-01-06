<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit\ExceptionConversion;

use Exception;
use Phauthentic\Symfony\ProblemDetails\ExceptionConversion\HttpExceptionConverter;
use Phauthentic\Symfony\ProblemDetails\ProblemDetailsFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *
 */
class HttpExceptionConverterTest extends TestCase
{
    public function testConvertExceptionToErrorDetails(): void
    {
        // Arrange
        $exception = new HttpException(404, 'Not Found');
        $converter = new HttpExceptionConverter(new ProblemDetailsFactory());
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        // Act
        $response = $converter->convertExceptionToErrorDetails($exception, $event);

        // Assert
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals([
            'status' => 404,
            'type' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404',
            'title' => 'Not Found',
        ], $data);
    }

    public function testCanHandle(): void
    {
        // Arrange
        $converter = new HttpExceptionConverter(new ProblemDetailsFactory());

        // Act
        $this->assertFalse($converter->canHandle(new Exception('Some other exception')));
        $this->assertTrue($converter->canHandle(new HttpException(404, 'Not Found')));
    }
}
