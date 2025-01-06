<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit;

use Exception;
use InvalidArgumentException;
use Phauthentic\Symfony\ProblemDetails\ExceptionConversion\GenericThrowableConverter;
use Phauthentic\Symfony\ProblemDetails\ProblemDetailsFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Phauthentic\Symfony\ProblemDetails\ThrowableToProblemDetailsKernelListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class ThrowableToProblemDetailsKernelListenerTest extends TestCase
{
    public static function providedEnvironments(): array
    {
        return [
            ['test', true],
            ['dev', true],
            ['prod', false],
        ];
    }

    #[Test]
    #[DataProvider('providedEnvironments')]
    public function testOnKernelException(string $environment, bool $shouldHaveTrace): void
    {
        // Arrange
        $throwable = new Exception('Unmapped exception');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request(
            server: ['HTTP_ACCEPT' => 'application/json']
        );
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $throwable);

        $listener = new ThrowableToProblemDetailsKernelListener(
            [
                new GenericThrowableConverter(new ProblemDetailsFactory(), $environment),
            ]
        );

        // Act
        $listener->onKernelException($event);

        // Assert
        $response = $event->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Unmapped exception', $response->getContent());
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if ($shouldHaveTrace) {
            $this->assertArrayHasKey('trace', $data);
        } else {
            $this->assertArrayNotHasKey('trace', $data);
        }
    }

    #[Test]
    public function testInstantiationWithoutConverters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one converter must be provided');

        new ThrowableToProblemDetailsKernelListener([]);
    }

    #[Test]
    public function testInstantiationWithoutValidConverter(): void
    {
        // Arrange
        $throwable = new Exception('Unmapped exception');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request(
            server: ['HTTP_ACCEPT' => 'application/json']
        );
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $throwable);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All converters must implement Phauthentic\Symfony\ProblemDetails\ExceptionConversion\ExceptionConverterInterface');

        // Act
        $listener = new ThrowableToProblemDetailsKernelListener([new \stdClass()]);
        $listener->onKernelException($event);
    }
}
