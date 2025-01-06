<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails;

use InvalidArgumentException;
use LogicException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @link https://www.rfc-editor.org/rfc/rfc9457.html
 */
class ProblemDetailsResponse extends JsonResponse
{
    protected static string $contentType = 'application/problem+json';

    /**
     * @var array<string>
     */
    protected static array $problemDetailsProtectedFields = [
        'status',
        'type',
        'title',
        'detail',
        'instance',
    ];

    /**
     * @param int $status
     * @param string $type
     * @param string|null $title
     * @param string|null $detail
     * @param string|null $instance
     * @param array<string, mixed> $extensions
     * @return self
     */
    public static function create(
        int $status,
        string $type = 'about:blank',
        ?string $title = null,
        ?string $detail = null,
        ?string $instance = null,
        array $extensions = []
    ): self {
        self::assertValidstatusCode($status);
        self::assertReservedResponseFields($extensions);

        $data = [
            'status' => $status,
            'type' => $type,
            'title' => $title,
        ];

        if (!empty($detail)) {
            $data['detail'] = $instance;
        }

        if (!empty($instance)) {
            $data['instance'] = $instance;
        }

        return new self(
            array_merge($data, $extensions),
            $status,
            [
                'Content-Type' => self::$contentType
            ]
        );
    }

    /**
     * @param array<string, mixed> $extensions
     */
    public static function assertReservedResponseFields(array $extensions): void
    {
        foreach (array_keys($extensions) as $key) {
            if (in_array($key, self::$problemDetailsProtectedFields, true)) {
                throw new InvalidArgumentException(sprintf(
                    'The key "%s" is a reserved key and cannot be used as an extension.',
                    $key
                ));
            }
        }
    }

    /**
     * Validates if the given status code is a valid client-side (4xx) or server-side (5xx) error.
     */
    protected static function assertValidStatusCode(?int $statusCode): void
    {
        if (!$statusCode) {
            return;
        }

        if (!($statusCode >= 400 && $statusCode < 500) && !($statusCode >= 500 && $statusCode < 600)) {
            throw new LogicException(sprintf(
                'Invalid status code %s provided for a Problem Details response. '
                . 'Status code must be a client (4xx) or server error (5xx) status code.',
                $statusCode
            ));
        }
    }
}
