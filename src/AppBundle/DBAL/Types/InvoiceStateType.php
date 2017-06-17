<?php

namespace AppBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class InvoiceStateType extends AbstractEnumType {
    const SENT = 'SENT';
    const PAID = 'PAID';

    // Need to change?
    // => https://github.com/fre5h/DoctrineEnumBundle#hook-for-doctrine-migrations

    protected static $choices = [
        self::SENT       => 'Sent',
        self::PAID    => 'Paid',
    ];
}
