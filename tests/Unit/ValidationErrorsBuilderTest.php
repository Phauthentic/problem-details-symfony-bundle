<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phauthentic\Symfony\ProblemDetails\Validation\ValidationErrorsBuilder;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationErrorsBuilderTest extends TestCase
{
    public function testBuildErrors(): void
    {
        // Arrange
        $violations = new ConstraintViolationList([
            new ConstraintViolation('must be a positive integer', null, [], null, 'age', null),
            new ConstraintViolation('must be \'green\', \'red\' or \'blue\'', null, [], null, 'profile.color', null),
        ]);
        $exception = new ValidationFailedException('Validation failed', $violations);
        $builder = new ValidationErrorsBuilder();

        // Act
        $errors = $builder->buildErrors($exception);

        // Assert
        $expectedErrors = [
            [
                'detail' => 'must be a positive integer',
                'pointer' => '#/age',
            ],
            [
                'detail' => 'must be \'green\', \'red\' or \'blue\'',
                'pointer' => '#/profile/color',
            ],
        ];

        $this->assertEquals($expectedErrors, $errors);
    }
}
