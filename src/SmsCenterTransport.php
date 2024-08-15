<?php

namespace Codewiser\SmsCenterMailer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class SmsCenterTransport extends AbstractTransport
{
    protected static bool $faked = false;

    public function __construct(protected SmsCenterContract $service, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        parent::__construct($dispatcher, $logger);
    }

    public static function fake(): void
    {
        self::$faked = true;
    }

    public function __toString(): string
    {
        return $this->service->name();
    }

    protected function doSend(SentMessage $message): void
    {
        $mail = MessageConverter::toEmail($message->getOriginalMessage());

        $request = $this->service->buildMessagePayload($mail);

        if (!self::$faked) {
            $response = $this->service->send($request);
        } else {
            $response = true;
        }

        $message->appendDebug(json_encode($response));
    }
}