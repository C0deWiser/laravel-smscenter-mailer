<?php

namespace Codewiser\SmsCenterMailer;

class SmsCenterServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsCenterService::class, function () {

            $config = config('mail.mailers.smsc', []);

            $request = \Illuminate\Support\Facades\Http::baseUrl($config['endpoint'] ?? 'https://smsc.ru/sys')
                ->withOptions($config['options'] ?? []);

            $service = new SmsCenterService($request, [
                'login'  => $config['username'] ?? '',
                'psw'    => $config['password'] ?? '',
                'sender' => config('mail.from.address')
            ], $config['secret']);

            if ($logger = config('mail.mailers.log.channel')) {
                $service->setLogger(logger()->channel($logger));
            }

            return $service;
        });

        $this->app->singleton(SmsCenterContract::class, fn() => app(SmsCenterService::class));
    }

    public function boot(): void
    {
        $this->registerResources();

        \Illuminate\Support\Facades\Mail::extend(
            'smsc',
            fn() => new SmsCenterTransport(app(SmsCenterContract::class))
        );
    }

    protected function registerResources(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'smsc-mailer');
    }
}