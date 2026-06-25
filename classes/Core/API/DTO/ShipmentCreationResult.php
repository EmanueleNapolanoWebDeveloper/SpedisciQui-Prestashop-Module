<?php

namespace SpedisciQui\DTO;

class ShipmentCreationResult
{

    private function __construct(
        private readonly bool    $success,
        private readonly ?string $errorMessage,
        private readonly ?string $trackingNumber,
        private readonly ?string $remoteShipmentId,
    ) {}

    public static function success(
        string $trackingNumber,
        string $remoteShipmentId
    ): self {
        return new self(true, null, $trackingNumber, $remoteShipmentId);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message, null, null);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }
    public function getRemoteShipmentId(): ?string
    {
        return $this->remoteShipmentId;
    }
}
