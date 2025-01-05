<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use Symfony\Component\HttpFoundation\Response;

interface ProblemDetailsFactoryInterface
{
    /**
     * @param int $status
     * @param string $type
     * @param string|null $title
     * @param string|null $instance
     * @param array<int, array<string, mixed>> $errors
     * @return Response
     * @link https://www.rfc-editor.org/rfc/rfc9457.html#name-members-of-a-problem-detail
     */
    public function createResponse(
        int $status,
        string $type = 'about:blank',
        ?string $title = null,
        ?string $instance = null,
        array $errors = []
    ): Response;
}
