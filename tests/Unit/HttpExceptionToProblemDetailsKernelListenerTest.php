<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Phauthentic\Symfony\ProblemDetails\HttpExceptionToProblemDetailsKernelListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class HttpExceptionToProblemDetailsKernelListenerTest extends TestCase
{
    public function testOnKernelExceptionWithHttpException(): void
    {
        // Arrange
        $exception = new HttpException(404, 'Not Found');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        $listener = new HttpExceptionToProblemDetailsKernelListener();

        // Act
        $listener->onKernelException($event);

        // Assert
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'type' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404',
                'title' => 'Not Found',
                'status' => 404,
            ], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }

    public function testOnKernelExceptionWithNonHttpException(): void
    {
        // Arrange
        $exception = new Exception('Some other exception');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        $listener = new HttpExceptionToProblemDetailsKernelListener();

        // Act
        $listener->onKernelException($event);

        // Assert
        $response = $event->getResponse();
        $this->assertNull($response);
    }
}
