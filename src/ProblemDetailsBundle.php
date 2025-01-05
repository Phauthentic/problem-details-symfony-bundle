<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use Phauthentic\Symfony\ProblemDetails\DependencyInjection\ProblemDetailsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @codeCoverageIgnore
 */
class ProblemDetailsBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ProblemDetailsExtension();
    }
}
