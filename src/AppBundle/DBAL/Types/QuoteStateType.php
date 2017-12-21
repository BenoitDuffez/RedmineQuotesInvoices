<?php

namespace AppBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class QuoteStateType extends AbstractEnumType {
	const DRAFT = 'DRAFT';
	const REJECTED = 'REJECTED';
	const REPLACED = 'REPLACED';
	const ACCEPTED = 'ACCEPTED';

	// Need to change?
	// => https://github.com/fre5h/DoctrineEnumBundle#hook-for-doctrine-migrations

	protected static $choices = [
		self::DRAFT => 'Draft',
		self::REJECTED => 'Rejected',
		self::REPLACED => 'Replaced',
		self::ACCEPTED => 'Accepted',
	];
}

