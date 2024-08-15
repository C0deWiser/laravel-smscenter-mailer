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
            self::undefined        => __('status.undefined'),
            self::not_found        => __('status.not_found'),
            self::cancelled        => __('status.cancelled'),
            self::pending          => __('status.pending'),
            self::sending          => __('status.sending'),
            self::delivered        => __('status.delivered'),
            self::read             => __('status.read'),
            self::expired          => __('status.expired'),
            self::link_reached     => __('status.link_reached'),
            self::spammed          => __('status.spammed'),
            self::failed           => __('status.failed'),
            self::wrong_route      => __('status.wrong_route'),
            self::denied           => __('status.denied'),
            self::payment_required => __('status.payment_required'),
            self::unreached        => __('status.unreached'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::undefined        => __('description.undefined'),
            self::not_found        => __('description.not_found'),
            self::cancelled        => __('description.cancelled'),
            self::pending          => __('description.pending'),
            self::sending          => __('description.sending'),
            self::delivered        => __('description.delivered'),
            self::read             => __('description.read'),
            self::expired          => __('description.expired'),
            self::link_reached     => __('description.link_reached'),
            self::spammed          => __('description.spammed'),
            self::failed           => __('description.failed'),
            self::wrong_route      => __('description.wrong_route'),
            self::denied           => __('description.denied'),
            self::payment_required => __('description.payment_required'),
            self::unreached        => __('description.unreached'),
        };
    }
}
