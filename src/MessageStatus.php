<?php

namespace Codewiser\SmsCenterMailer;

enum MessageStatus: int
{
    case undefined = -65365;
    case not_found = -3;
    case cancelled = -2;
    case pending = -1;
    case sending = 0;
    case delivered = 1;
    case read = 2;
    case expired = 3;
    case link_reached = 4;
    case spammed = 6;
    case failed = 20;
    case wrong_route = 22;
    case denied = 23;
    case payment_required = 24;
    case unreached = 25;

    /**
     * Not final statuses.
     */
    public static function transient(): array
    {
        return array_filter(self::cases(), fn(self $status) => !$status->final());
    }

    public function status(): string
    {
        return match ($this) {
            self::pending      => 'info',
            self::sending,
            self::delivered,
            self::read,
            self::link_reached => 'success',
            self::cancelled    => 'warning',
            self::undefined,
            self::expired,
            self::failed,
            self::not_found,
            self::wrong_route,
            self::denied,
            self::spammed,
            self::payment_required,
            self::unreached    => 'danger',
        };
    }

    /**
     * Successful, but not final statuses.
     */
    public function successful(): bool
    {
        return match ($this) {
            self::sending,
            self::pending,
            self::read,
            self::link_reached => true,
            default            => false
        };
    }

    /**
     * The very final statuses.
     */
    public function final(): bool
    {
        return match ($this) {
            self::pending,
            self::sending,
            self::delivered,
            self::read,
            self::payment_required => false,
            default                => true
        };
    }

    public function caption(): string
    {
        return match ($this) {
            self::undefined        => __('smsc-mailer::status.undefined.caption'),
            self::not_found        => __('smsc-mailer::status.not_found.caption'),
            self::cancelled        => __('smsc-mailer::status.cancelled.caption'),
            self::pending          => __('smsc-mailer::status.pending.caption'),
            self::sending          => __('smsc-mailer::status.sending.caption'),
            self::delivered        => __('smsc-mailer::status.delivered.caption'),
            self::read             => __('smsc-mailer::status.read.caption'),
            self::expired          => __('smsc-mailer::status.expired.caption'),
            self::link_reached     => __('smsc-mailer::status.link_reached.caption'),
            self::spammed          => __('smsc-mailer::status.spammed.caption'),
            self::failed           => __('smsc-mailer::status.failed.caption'),
            self::wrong_route      => __('smsc-mailer::status.wrong_route.caption'),
            self::denied           => __('smsc-mailer::status.denied.caption'),
            self::payment_required => __('smsc-mailer::status.payment_required.caption'),
            self::unreached        => __('smsc-mailer::status.unreached.caption'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::undefined        => __('smsc-mailer::status.undefined.description'),
            self::not_found        => __('smsc-mailer::status.not_found.description'),
            self::cancelled        => __('smsc-mailer::status.cancelled.description'),
            self::pending          => __('smsc-mailer::status.pending.description'),
            self::sending          => __('smsc-mailer::status.sending.description'),
            self::delivered        => __('smsc-mailer::status.delivered.description'),
            self::read             => __('smsc-mailer::status.read.description'),
            self::expired          => __('smsc-mailer::status.expired.description'),
            self::link_reached     => __('smsc-mailer::status.link_reached.description'),
            self::spammed          => __('smsc-mailer::status.spammed.description'),
            self::failed           => __('smsc-mailer::status.failed.description'),
            self::wrong_route      => __('smsc-mailer::status.wrong_route.description'),
            self::denied           => __('smsc-mailer::status.denied.description'),
            self::payment_required => __('smsc-mailer::status.payment_required.description'),
            self::unreached        => __('smsc-mailer::status.unreached.description'),
        };
    }
}
