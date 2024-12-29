<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Validation;

use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Builds an errors array matching the multiple errors example from RFC 9457.
 *
 * Example:
 * <pre>
 * {
 *  "type": "https://example.net/validation-error",
 *  "title": "Your request is not valid.",
 *  "errors": [
 *              {
 *                "detail": "must be a positive integer",
 *                "pointer": "#/age"
 *              },
 *              {
 *                "detail": "must be 'green', 'red' or 'blue'",
 *                "pointer": "#/profile/color"
 *              }
 *           ]
 * }
 * </pre>
 *
 * @link https://www.rfc-editor.org/rfc/rfc9457.html#name-the-problem-details-json-ob
 */
class ValidationErrorsBuilder implements ValidationErrorsBuilderInterface
{
    public function buildErrors(ValidationFailedException $exception): array
    {
        $errors = [];
        foreach ($exception->getViolations() as $violation) {
            $errors[] = [
                'detail' => $violation->getMessage(),
                'pointer' => $this->convertToJsonPointer($violation->getPropertyPath())
            ];
        }

        return $errors;
    }

    private function convertToJsonPointer(string $propertyPath): string
    {
        $jsonPointer = preg_replace('/\[(\d+)\]/', '/$1', $propertyPath);
        $jsonPointer = str_replace('.', '/', (string)$jsonPointer);

        return '#/' . $jsonPointer;
    }
}
