<?php

namespace Codewiser\SmsCenterMailer;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class SmsCenterService implements SmsCenterContract
{
    protected ?LoggerInterface $logger = null;

    public function __construct(
        protected PendingRequest $pendingRequest,
        protected array $params,
        protected string $secret
    ) {
        //
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function name(): string
    {
        return 'smsc';
    }

    public function webhookStatus(array $request): void
    {
        $request['id'] = $request['id'] ?? '';
        $request['to'] = $request['to'] ?? '';
        $request['ts'] = $request['ts'] ?? '';
        $request['md5'] = $request['md5'] ?? '';
        $request['flag'] = $request['flag'] ?? null;
        $request['phone'] = $request['phone'] ?? '';
        $request['status'] = $request['status'] ?? '';

        try {
            $valid = false;
            // for ingoing message
            if (isset($request['mes'])) {
                $valid = md5($request['phone'].":".$request['mes'].":".$request['to'].":".$this->secret) === $request['md5'];
            } // to verify number with phone call
            elseif (isset($request['waitcall'])) {
                $valid = md5($request['phone'].":".$request['ts'].":".$this->secret) === $request['md5'];
            } // for delivery status
            else {
                $valid = md5($request['id'].":".$request['phone'].":".$request['status'].":".$this->secret) === $request['md5'];
            }
            if (!$valid) {
                throw new Exception('hash is invalid');
            }
        } catch (Exception $e) {
            $this->logger?->error('webhook/smsc: '.$e->getMessage(), $request);
            throw $e;
        }

        try {

            if (!$request['status']) {
                throw new Exception('no action if no status');
            }

            if (isset($request['type'])) {
                $types = [
                    "0" => "SMS", "1" => "Flash-SMS", "2" => "Бинарное SMS", "3" => "Wap-push", "4" => "HLR-запрос",
                    "5" => "Ping-SMS", "6" => "MMS", "7" => "Звонок", "10" => "Viber", "12" => "Соцсети"
                ];
                // It looks like we need 8 code
                $type = $request['type'];

                if (isset($types[$type])) {
                    $this->logger?->info('webhook/smsc: is not email', $request);
                } else {
                    if ($type == 8 || $request['flag'] == 8 || $request['flag'] == 40) {
                        $status = MessageStatus::tryFrom($request['type']) ?? MessageStatus::undefined;

                        event(
                            new SmsCenterStatusEvent($request['id'], $status)
                        );

                    } else {
                        throw new Exception('unknown smsc type/flag ('.$type.'/'.$request['flag'].') in callback');
                    }
                }
            } else {
                throw new Exception('unknown smsc type in callback');
            }

        } catch (Exception $e) {
            $this->logger?->error('webhook/smsc: '.$e->getMessage(), $request);
        }

    }

    public function buildMessagePayload(Email $email): array
    {
        $recipients = array_map(fn(Address $address) => $address
            ->getAddress(), $email->getTo());

        $params = [
            // send as email
            'mail'    => 1,
            'charset' => $email->getHtmlCharset(),
            'subj'    => $email->getSubject(),
            'phones'  => implode(';', $recipients),
            'mes'     => $email->getHtmlBody(),
        ];

        if ($sender = $email->getSender()) {
            $params['sender'] = $sender->getAddress();
        }

        $params["attachments"] = $email->getAttachments();

        if (count($recipients) > 1) {
            // Mass sending route
            $params['path'] = 'jobs.php';
            // Command to create queue
            $params['add'] = 1;
            // Name of mass sending
            $params['name'] = $email->getSubject();
        }

        return $params;
    }

    public function buildMailOutStatusPayload(int $message_id): array
    {
        return [
            'path' => 'jobs.php',
            'mail' => 1,
            'get'  => 1,
            'id'   => $message_id,
        ];
    }

    public function buildSingleStatusPayload(int $message_id, string $route): array
    {
        return [
            'path'  => 'status.php',
            'id'    => $message_id,
            'phone' => $route,
        ];
    }

    public function send(array $request): array
    {
        $params = $request + $this->params;
        $params['charset'] = $params['charset'] ?? 'utf-8';
        // response format - json
        $params['fmt'] = 3;

        // service endpoint
        // jobs.php mass
        // send.php single
        $path = $params['path'] ?? 'send.php';

        if (isset($params['path'])) {
            unset($params['path']);
        }

        $request = $this->pendingRequest;

        $attachments = $params["attachments"] ?? [];
        if (isset($params["attachments"])) {
            unset($params["attachments"]);
        }

        /** @var DataPart $attachment */
        foreach ($attachments as $i => $attachment) {
            $request->attach("file$i",
                $attachment->getBody(),
                $attachment->getName(),
                $attachment->getPreparedHeaders()->toArray()
            );
        }

        if (!$attachments) {
            $request->asForm();
        }

        $response = $request->post($path, $params);

        $params['path'] = $path;

        $this->debug($params, $response, $attachments);

        return $response->throw()->json();
    }

    /**
     * @param  array  $request
     * @param  \Illuminate\Http\Client\Response  $response
     * @param  array<int, DataPart>  $attachments
     *
     * @return void
     */
    protected function debug(array $request, \Illuminate\Http\Client\Response $response, array $attachments): void
    {
        $request['psw'] = str($request['psw'])->mask('*', 0)->toString();

        $request['files'] = array_map(fn(DataPart $part, $i) => ["file$i" => $part->getName()], $attachments,
            array_keys($attachments));

        if ($response->failed()) {
            $response = [
                'status'        => $response->status(),
                'error_code'    => $response->toException()->getCode(),
                'error_message' => $response->toException()->getMessage(),
                'body'          => $response->body(),
            ];

            $this->logger?->error(class_basename($this), ['request' => $request, 'response' => $response]);
        } else {
            $response = [
                'status' => $response->status(),
                'json'   => $response->json()
            ];

            $this->logger?->debug(class_basename($this), ['request' => $request, 'response' => $response]);

            unset($request['mes']);
            $this->logger?->info(class_basename($this), ['request' => $request, 'response' => $response]);
        }
    }

    public function throw(array $response): array
    {
        if (isset($response['error'])) {
            throw new SmsCenterException($response['error'], $response['error_code'] ?? 0);
        }

        return $response;
    }
}
