<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Validation;

use Symfony\Component\Validator\Exception\ValidationFailedException;

interface ValidationErrorsBuilderInterface
{
    /**
     * Builds an array of errors from a ValidationFailedException
     *
     * @param ValidationFailedException $exception
     * @return array<int, array<string, mixed>>
     */
    public function buildErrors(ValidationFailedException $exception): array;
}
