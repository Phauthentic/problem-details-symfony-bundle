<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use Symfony\Component\HttpFoundation\Response;

interface ProblemDetailsFactoryInterface
{
    /**
     * @param string $type
     * @param string $title
     * @param int $status
     * @param string $instance
     * @param array<int, array<string, mixed>> $errors
     * @link https://www.rfc-editor.org/rfc/rfc9457.html#name-members-of-a-problem-detail
     */
    public function createResponse(
        string $type,
        string $title,
        int $status,
        string $instance,
        array $errors = []
    ): Response;
}
