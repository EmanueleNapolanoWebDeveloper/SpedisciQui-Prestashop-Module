<?php

namespace SpedisciQui\DTO;

class ApiResponse
{

    private function __construct(
        private readonly bool  $success,
        private readonly int   $statusCode,
        private readonly array $data,
        private readonly ?string $errorMessage,
        private readonly ?string $errorType,   // 'auth' | 'network' | 'server' | 'parse'
    ) {}



    public static function success(int $statusCode, array $data): self
    {
        return new self(true, $statusCode, $data, null, null);
    }

    public static function failure(
        int $statusCode,
        string $errorMessage,
        string $errorType
    ): self {
        return new self(false, $statusCode, [], $errorMessage, $errorType);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    public function getData(): array
    {
        return $this->data;
    }
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
    public function getErrorType(): ?string
    {
        return $this->errorType;
    }
    public function isAuthError(): bool
    {
        return $this->errorType === 'auth';
    }
    public function isNetworkError(): bool
    {
        return $this->errorType === 'network';
    }
}
