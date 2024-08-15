# SMS Center mailer for Laravel

This package brings one more mailer to your Laravel project —
https://smsc.ru service.

It supports as personal, as mass sending of emails.

## Disclaimer

This mailer was tested with very specific tasks, so we can not guarantee
that it will meet your expectations.

## Installation

Just install package from composer.

## Configuration

Add `smsc` section to `mail.mailers` config of your application:

```php
'smsc' => [
    'transport' => 'smsc',
    'endpoint'  => env('SMSC_URL', 'https://smsc.ru/sys'),
    'username'  => env('SMSC_LOGIN'),
    'password'  => env('SMSC_PASSWORD'),
    'secret'    => env('SMSC_SECRET'),
],
```

Service will write info/error logs to channel defined in `mail.mailers.log.channel` config.

Finally, set `MAIL_MAILER=smsc` to your `.env` file.

## Mass sending

Compose `Mailable` with more than one recipient and just
send it:

```php
use Illuminate\Support\Facades\Mail;

$recipients = [
    'foo@example.com',
    'bar@example.com',
];

Mail::send(new CustomMailable($recipients));
```

## Personal sending

Compose `Mailable` with only one recipient or use `Notification`.

## Getting response

The only way to pass mailer response through facade back to the application
(that I found) it to append response as a debug of
`\Symfony\Component\Mailer\SentMessage`:

```php
use Codewiser\SmsCenterMailer\SmsCenterContract;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Events\MessageSent;

$recipients = [
    'foo@example.com',
    'bar@example.com',
];

Event::listen(MessageSent::class, function (MessageSent $event) {

    $service = app(SmsCenterContract::class);
    
    // json encoded response
    $response = json_decode($event->sent->getDebug(), true);
    
    // Die if service respond with error
    $service->throw($response);
    
    // Keep $response['id'] for later use
});

Mail::send(new CustomMailable($recipients));
```

## Message delivery status

After message was sent we may want to check its delivery status.

### Poll

We may utilize `SmsCenterService` to examine message by its id.

Command:
```php
use Codewiser\SmsCenterMailer\MessageStatus;
use Codewiser\SmsCenterMailer\SmsCenterContract;
use Codewiser\SmsCenterMailer\SmsCenterStatusEvent;

/**
 * Execute the console command.
 */
public function handle(SmsCenterContract $provider): void
{   
    // For single messages
    $request = $provider->buildSingleStatusPayload(
        message_id: $this->argument('id'),
        route: $this->argument('email')
    );
    
    // For mass sending
    $request = $provider->buildMailOutStatusPayload(
        message_id: $this->argument('id')
    );
    
    // Die if service respond with error
    $response = $provider->throw(
        $provider->send($request)
    );
    
    // Get status
    $status = MessageStatus::tryFrom($response['status']) ?? MessageStatus::undefined;
    
    // Update status or fire event (see Push)
    event(new SmsCenterStatusEvent($this->argument('id'), $status));
}
```

### Push

SMS Center may 
[call webhook](https://smsc.ru/api/http/miscellaneous/callback/), 
if you register it. Ingoing request will be 
validated and possibly `SmsCenterStatusEvent` will be fired.

Controller:
```php
use Codewiser\SmsCenterMailer\SmsCenterContract;
use Illuminate\Http\Request;

public function webhook(Request $request, SmsCenterContract $service) {
    
    $service->webhookStatus($request->all());
    
    return response('ok');
}
```

Event listener:
```php
use Codewiser\SmsCenterMailer\SmsCenterStatusEvent;

/**
 * Handle the event.
 */
public function handle(SmsCenterStatusEvent $event): void
{
    // Find message by $event->id
    // Update message status with $event->status
}
```