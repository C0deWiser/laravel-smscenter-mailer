<?php


namespace Codewiser\SmsCenterMailer;

use App\Services\Smscenter\Events\SmsCenterStatusEvent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\Mime\Email;
use Throwable;

/**
 * Send requests to SmsCenter
 */
interface SmsCenterContract
{
    public function name(): string;

    /**
     * Validates ingoing webhook message. May fire SmsCenterStatusEvent or throw an exception.
     *
     * @see https://smsc.ru/api/http/miscellaneous/callback/
     *
     * @throws Throwable
     */
    public function webhookStatus(array $request): void;

    /**
     * Build a request payload from Symfony object.
     */
    public function buildMessagePayload(Email $email): array;

    /**
     * Build a payload to get mail-out status.
     */
    public function buildMailOutStatusPayload(int $message_id): array;

    /**
     * Build a payload to get single message status.
     */
    public function buildSingleStatusPayload(int $message_id, string $route): array;

    /**
     * Send a request payload.
     *
     * @throws Throwable
     */
    public function send(array $request): array;

    /**
     * Inspect response and throw an exception if deserved.
     *
     * @throws SmsCenterException
     */
    public function throw(array $response): array;
}
