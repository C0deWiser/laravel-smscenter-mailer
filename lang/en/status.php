<?php

return [
    'undefined'        => [
        'caption'     => 'Undefined',
        'description' => 'Status unknown or undefined.'
    ],
    'not_found'        => [
        'caption'     => 'Not Found',
        'description' => 'Phone number and message ID was not found.'
    ],
    'cancelled'        => [
        'caption'     => 'Cancelled',
        'description' => 'Mass delivery was manually cancelled.'
    ],
    'pending'          => [
        'caption'     => 'Pending',
        'description' => 'Message is waiting to be sent.'
    ],
    'sending'          => [
        'caption'     => 'Sending',
        'description' => 'Message is sending now.'
    ],
    'delivered'        => [
        'caption'     => 'Delivered',
        'description' => 'Message was successfully delivered.'
    ],
    'read'             => [
        'caption'     => 'Was read',
        'description' => 'Message was read (opened).'
    ],
    'expired'          => [
        'caption'     => 'Expired',
        'description' => 'Message was not sent in a proper time.'
    ],
    'link_reached'     => [
        'caption'     => 'Link was reached',
        'description' => 'Message was delivered and recipient followed a link.'
    ],
    'spammed'          => [
        'caption'     => 'Spammed',
        'description' => 'Message was rejected by recipient\'s server.'
    ],
    'failed'           => [
        'caption'     => 'Failed',
        'description' => 'Sending message was failed.'
    ],
    'wrong_route'      => [
        'caption'     => 'Wrong route',
        'description' => 'Wrong phone number.'
    ],
    'denied'           => [
        'caption'     => 'Denied',
        'description' => 'Too many tries to send the same message, or flood, or recipient is black listed, or message has restricted content.'
    ],
    'payment_required' => [
        'caption'     => 'Payment required',
        'description' => 'Our gold mine is running low. We need more gold!'
    ],
    'unreached'        => [
        'caption'     => 'Unreachable',
        'description' => 'Can\'t find route to recipient.'
    ],
];