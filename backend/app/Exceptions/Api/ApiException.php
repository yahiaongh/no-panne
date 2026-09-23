<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use RuntimeException;

/**
 * Business-rule exception rendered as a JSON API error.
 * Carries an HTTP status, a stable machine-readable code and optional field errors.
 */
class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        int $status = 422,
        protected string $errorCode = 'business_rule',
        protected ?array $errors = null,
    ) {
        parent::__construct($message, $status);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function errors(): ?array
    {
        return $this->errors;
    }
}