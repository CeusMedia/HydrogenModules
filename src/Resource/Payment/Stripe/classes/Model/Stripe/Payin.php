<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Stripe_Payin extends Model
{
	const STATUS_UNKNOWN			= 0;
	const STATUS_CREATED			= 1;
	const STATUS_FAILED				= 2;
	const STATUS_SUCCEEDED			= 3;

	const TYPE_UNKNOWN				= 0;
	const TYPE_CARD					= 1;
	const TYPE_PREAUTHORIZED		= 2;
	const TYPE_BANK_WIRE			= 3;
	const TYPE_DIRECT_DEBIT			= 4;
	const TYPE_DIRECT_DEBIT_DIRECT	= 5;
	const TYPE_PAYPAL				= 6;

	protected string $name		= 'stripe_payins';

	protected array $columns	= [
		"payinId",
		"userId",
		"status",
		"id",
		"type",
		"amount",
		"currency",
		"data",
		"createdAt",
		"modifiedAt"
	];

	protected string $primaryKey	= 'payinId';

	protected array $indices		= [
		"userId",
		"status",
		"id",
		"type",
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;

	public static function getLatestResourceFromPayinData( $payinData )
	{
		$resource	= json_decode( $payinData );
		$keys		= array_keys( (array) $resource );
		while( $key = array_pop( $keys ) ){
			if( $resource->{$key} )
				return $resource->{$key};
		}
		return NULL;
	}

	public static function getStatusId( $status ): int
	{
		switch( $status ){
			case 'CREATED':
				return self::STATUS_CREATED;
			case 'FAILED':
				return self::STATUS_FAILED;
			case 'SUCCEEDED':
				return self::STATUS_SUCCEEDED;
		}
		return self::STATUS_UNKNOWN;
	}

	public static function getStatusLabel( int $status ): string
	{
		switch( $status ){
			case self::STATUS_CREATED:
				return 'CREATED';
			case self::STATUS_FAILED:
				return 'FAILED';
			case self::STATUS_SUCCEEDED:
				return 'SUCCEEDED';
		}
		return 'UNKNOWN';
	}

	public static function getTypeId( string $type ): int
	{
		switch( $type ){
			case 'CARD':
				return self::TYPE_CARD;
			case 'PREAUTHORIZED':
				return self::TYPE_PREAUTHORIZED;
			case 'BANK_WIRE':
				return self::TYPE_BANK_WIRE;
			case 'DIRECT_DEBIT':
				return self::TYPE_DIRECT_DEBIT;
			case 'DIRECT_DEBIT_DIRECT':
				return self::TYPE_DIRECT_DEBIT_DIRECT;
			case 'PAYPAL':
				return self::TYPE_PAYPAL;
		}
		return self::TYPE_UNKNOWN;
	}

	public static function getTypeLabel( int $type ): string
	{
		switch( $type ){
			case self::TYPE_CARD:
				return 'CARD';
			case self::TYPE_PREAUTHORIZED:
				return 'PREAUTHORIZED';
			case self::TYPE_BANK_WIRE:
				return 'BANK_WIRE';
			case self::TYPE_DIRECT_DEBIT:
				return 'DIRECT_DEBIT';
			case self::TYPE_DIRECT_DEBIT_DIRECT:
				return 'DIRECT_DEBIT_DIRECT';
			case self::TYPE_PAYPAL:
				return 'PAYPAL';
		}
		return 'UNKNOWN';
	}
}
