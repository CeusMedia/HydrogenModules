<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Stripe_Payin extends Model
{
	public const STATUS_UNKNOWN				= 0;
	public const STATUS_CREATED				= 1;
	public const STATUS_FAILED				= 2;
	public const STATUS_SUCCEEDED			= 3;

	public const TYPE_UNKNOWN				= 0;
	public const TYPE_CARD					= 1;
	public const TYPE_PREAUTHORIZED			= 2;
	public const TYPE_BANK_WIRE				= 3;
	public const TYPE_DIRECT_DEBIT			= 4;
	public const TYPE_DIRECT_DEBIT_DIRECT	= 5;
	public const TYPE_PAYPAL				= 6;

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
		return match( $status ){
			'CREATED'		=> self::STATUS_CREATED,
			'FAILED'		=> self::STATUS_FAILED,
			'SUCCEEDED'		=> self::STATUS_SUCCEEDED,
			default			=> self::STATUS_UNKNOWN,
		};
	}

	public static function getStatusLabel( int $status ): string
	{
		return match( $status ){
			self::STATUS_CREATED	=> 'CREATED',
			self::STATUS_FAILED		=> 'FAILED',
			self::STATUS_SUCCEEDED	=> 'SUCCEEDED',
			default					=> 'UNKNOWN',
		};
	}

	public static function getTypeId( string $type ): int
	{
		return match( $type ){
			'CARD'					=> self::TYPE_CARD,
			'PREAUTHORIZED'			=> self::TYPE_PREAUTHORIZED,
			'BANK_WIRE'				=> self::TYPE_BANK_WIRE,
			'DIRECT_DEBIT'			=> self::TYPE_DIRECT_DEBIT,
			'DIRECT_DEBIT_DIRECT'	=> self::TYPE_DIRECT_DEBIT_DIRECT,
			'PAYPAL'				=> self::TYPE_PAYPAL,
			default					=> self::TYPE_UNKNOWN,
		};
	}

	public static function getTypeLabel( int $type ): string
	{
		return match( $type ){
			self::TYPE_CARD					=> 'CARD',
			self::TYPE_PREAUTHORIZED		=> 'PREAUTHORIZED',
			self::TYPE_BANK_WIRE			=> 'BANK_WIRE',
			self::TYPE_DIRECT_DEBIT			=> 'DIRECT_DEBIT',
			self::TYPE_DIRECT_DEBIT_DIRECT	=> 'DIRECT_DEBIT_DIRECT',
			self::TYPE_PAYPAL				=> 'PAYPAL',
			default							=> 'UNKNOWN',
		};
	}
}
