<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit;

use Phauthentic\Symfony\ProblemDetails\ThrowableToProblemDetailsKernelListener;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Phauthentic\Symfony\ProblemDetails\ExceptionConversion\ValidationFailedExceptionConverter;
use Phauthentic\Symfony\ProblemDetails\ExceptionConversion\HttpExceptionConverter;
use Phauthentic\Symfony\ProblemDetails\ExceptionConversion\GenericExceptionConverter;

/**
 *
 */
class ServiceLoadingTest extends TestCase
{
    public function testCreateValidResponse(): void
    {
        // Arrange
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
        $container->compile();

        // Assert
        $listener = $container->get(ThrowableToProblemDetailsKernelListener::class);
        $this->assertInstanceOf(ThrowableToProblemDetailsKernelListener::class, $listener);

        $reflectionClass = new ReflectionClass($listener);
        $converters = $reflectionClass->getProperty('exceptionConverters')->getValue($listener);
        $result = [];
        foreach ($converters as $converter) {
            $result[] = get_class($converter);
        }
        $this->assertCount(3, $result);
        $this->assertEquals([
            ValidationFailedExceptionConverter::class,
            HttpExceptionConverter::class,
            GenericExceptionConverter::class,
        ], $result);
    }
}
