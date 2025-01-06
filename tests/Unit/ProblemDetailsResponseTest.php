<?php

declare(strict_types=1);

namespace Phauthentic\Symfony\ProblemDetails\Tests\Unit;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Phauthentic\Symfony\ProblemDetails\ProblemDetailsResponse;

class ProblemDetailsResponseTest extends TestCase
{
    public function testCreateValidResponse(): void
    {
        // Arrange
        $status = 422;
        $type = 'https://example.com/probs/out-of-credit';
        $title = 'You do not have enough credit.';
        $detail = 'Your current balance is 30, but that costs 50.';
        $instance = '/account/12345/msgs/abc';
        $extensions = ['balance' => 30, 'accounts' => ['/account/12345', '/account/67890']];

        // Act
        $response = ProblemDetailsResponse::create(
            status: $status,
            type: $type,
            title: $title,
            detail: $detail,
            instance: $instance,
            extensions: $extensions
        );

        // Assert
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'status' => $status,
                'type' => $type,
                'title' => $title,
                'detail' => $detail,
                'instance' => $instance,
                'balance' => 30,
                'accounts' => ['/account/12345', '/account/67890']
            ], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }

    public function testCreateResponseWithReservedFieldInExtensions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The key "status" is a reserved key and cannot be used as an extension.');

        // Arrange
        $status = 422;
        $type = 'https://example.com/probs/out-of-credit';
        $title = 'You do not have enough credit.';
        $detail = 'Your current balance is 30, but that costs 50.';
        $instance = '/account/12345/msgs/abc';
        $extensions = ['status' => 'reserved'];

        // Act
        ProblemDetailsResponse::create(
            status: $status,
            type: $type,
            title: $title,
            detail: $detail,
            instance: $instance,
            extensions: $extensions
        );
    }

    public function testCreateResponseWithInvalidStatusCode(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid status code 200 provided for a Problem Details response.');

        // Arrange
        $status = 200;
        $type = 'https://example.com/probs/out-of-credit';
        $title = 'You do not have enough credit.';
        $detail = 'Your current balance is 30, but that costs 50.';
        $instance = '/account/12345/msgs/abc';

        // Act
        ProblemDetailsResponse::create(
            status: $status,
            type: $type,
            title: $title,
            detail: $detail,
            instance: $instance
        );
    }

    public function testCreateResponseWithMinimalParameters(): void
    {
        // Arrange
        $status = 500;

        // Act
        $response = ProblemDetailsResponse::create($status);

        // Assert
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'status' => $status,
                'type' => 'about:blank',
                'title' => null,
            ], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }
}
