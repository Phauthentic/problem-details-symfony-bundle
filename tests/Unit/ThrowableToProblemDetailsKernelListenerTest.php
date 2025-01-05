<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Phauthentic\Symfony\ProblemDetails\ThrowableToProblemDetailsKernelListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 *
 */
class ThrowableToProblemDetailsKernelListenerTest extends TestCase
{
    public function testOnKernelExceptionWithMappedThrowable(): void
    {
        // Arrange
        $throwable = new \RuntimeException('Mapped exception');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $throwable);

        $mapper = function (Throwable $t) {
            return new JsonResponse(['type' => 'about:blank', 'title' => $t->getMessage(), 'status' => 400], 400);
        };

        $listener = new ThrowableToProblemDetailsKernelListener('prod', [\RuntimeException::class => $mapper]);

        // Act
        $listener->onKernelException($event);

        // Assert
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['type' => 'about:blank', 'title' => 'Mapped exception', 'status' => 400], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }

    public function testOnKernelExceptionWithUnmappedThrowableInProd(): void
    {
        // Arrange
        $throwable = new Exception('Unmapped exception');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $throwable);

        $listener = new ThrowableToProblemDetailsKernelListener('prod');

        // Act
        $listener->onKernelException($event);

        // Assert
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['type' => 'about:blank', 'title' => 'Unmapped exception', 'status' => 500], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }

    public function testOnKernelExceptionWithUnmappedThrowableInDev(): void
    {
        // Arrange
        $throwable = new Exception('Unmapped exception');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $throwable);

        $listener = new ThrowableToProblemDetailsKernelListener('dev');

        // Act
        $listener->onKernelException($event);

        // Assert
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Unmapped exception', $response->getContent());
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertNotEmpty($data['trace']);
    }
}
