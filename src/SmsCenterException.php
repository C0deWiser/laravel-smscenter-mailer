<?php

namespace Codewiser\SmsCenterMailer;

use Throwable;

class SmsCenterException extends \Exception
{
    protected ?int $message_id;

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, int $message_id = null) {
        parent::__construct($message, $code, $previous);

        $this->message_id = $message_id;
    }

    public function getMessageId(): ?int
    {
        return $this->message_id;
    }
}