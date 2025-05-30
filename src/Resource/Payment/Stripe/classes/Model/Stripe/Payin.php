<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Stripe_Payin extends Model
{
	public const STATUS_UNKNOWN				= 0;
	public const STATUS_CREATED				= 1;
	public const STATUS_FAILED				= 2;
	public const STATUS_SUCCEEDED			= 3;

	public const STATUSES					= [
		self::STATUS_UNKNOWN,
		self::STATUS_CREATED,
		self::STATUS_FAILED,
		self::STATUS_SUCCEEDED,
	];

	public const STATUS_LABEL_UNKNOWN		= 'UNKNOWN';
	public const STATUS_LABEL_CREATED		= 'CREATED';
	public const STATUS_LABEL_FAILED		= 'FAILED';
	public const STATUS_LABEL_SUCCEEDED		= 'SUCCEEDED';

	public const STATUS_LABELS				= [
		self::STATUS_LABEL_UNKNOWN,
		self::STATUS_LABEL_CREATED,
		self::STATUS_LABEL_FAILED,
		self::STATUS_LABEL_SUCCEEDED,
	];
	public const STATUS_MAP					= [
		self::STATUS_UNKNOWN		=> self::STATUS_LABEL_UNKNOWN,
		self::STATUS_CREATED		=> self::STATUS_LABEL_CREATED,
		self::STATUS_FAILED			=> self::STATUS_LABEL_FAILED,
		self::STATUS_SUCCEEDED		=> self::STATUS_LABEL_SUCCEEDED,
	];

	public const TYPE_UNKNOWN				= 0;
	public const TYPE_CARD					= 1;
	public const TYPE_PREAUTHORIZED			= 2;
	public const TYPE_BANK_WIRE				= 3;
	public const TYPE_DIRECT_DEBIT			= 4;
	public const TYPE_DIRECT_DEBIT_DIRECT	= 5;
	public const TYPE_PAYPAL				= 6;

	public const TYPES						= [
		self::TYPE_UNKNOWN,
		self::TYPE_CARD,
		self::TYPE_PREAUTHORIZED,
		self::TYPE_BANK_WIRE,
		self::TYPE_DIRECT_DEBIT,
		self::TYPE_DIRECT_DEBIT_DIRECT,
		self::TYPE_PAYPAL,
	];


	public const TYPE_LABEL_CARD				= 'CARD';
	public const TYPE_LABEL_PREAUTHORIZED		= 'PREAUTHORIZED';
	public const TYPE_LABEL_BANK_WIRE			= 'BANK_WIRE';
	public const TYPE_LABEL_DIRECT_DEBIT		= 'DIRECT_DEBIT';
	public const TYPE_LABEL_DIRECT_DEBIT_DIRECT	= 'DIRECT_DEBIT_DIRECT';
	public const TYPE_LABEL_PAYPAL				= 'PAYPAL';
	public const TYPE_LABEL_UNKNOWN				= 'UNKNOWN';

	public const TYPE_LABELS				= [
		self::TYPE_LABEL_CARD,
		self::TYPE_PREAUTHORIZED,
		self::TYPE_LABEL_BANK_WIRE,
		self::TYPE_LABEL_DIRECT_DEBIT,
		self::TYPE_LABEL_DIRECT_DEBIT_DIRECT,
		self::TYPE_LABEL_PAYPAL,
		self::TYPE_LABEL_UNKNOWN,
	];

	public const TYPE_MAP					= [
		self::TYPE_CARD						=> self::TYPE_LABEL_CARD,
		self::TYPE_PREAUTHORIZED			=> self::TYPE_PREAUTHORIZED,
		self::TYPE_BANK_WIRE				=> self::TYPE_LABEL_BANK_WIRE,
		self::TYPE_DIRECT_DEBIT				=> self::TYPE_LABEL_DIRECT_DEBIT,
		self::TYPE_DIRECT_DEBIT_DIRECT		=> self::TYPE_LABEL_DIRECT_DEBIT_DIRECT,
		self::TYPE_PAYPAL					=> self::TYPE_LABEL_PAYPAL,
		self::TYPE_UNKNOWN					=> self::TYPE_LABEL_UNKNOWN,
	];

	protected string $name		= 'stripe_payins';

	protected array $columns	= [
		'payinId',
		'userId',
		'status',
		'id',
		'type',
		'amount',
		'currency',
		'data',
		'createdAt',
		'modifiedAt'
	];

	protected string $primaryKey	= 'payinId';

	protected array $indices		= [
		'userId',
		'status',
		'id',
		'type',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

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

	/**
	 *	Resolves status label to status integer ID.
	 *	@param		string		$status		Status label
	 *	@return		int						Status integer ID
	 *	@throws		DomainException			if given status is not existing
	 */
	public static function getStatusId( string $status ): int
	{
		if( !in_array(	$status, self::STATUS_LABELS, TRUE ) )
			throw new DomainException( 'Invalid status' );
		return array_flip( self::STATUS_MAP )[$status];
	}

	/**
	 *	Resolves status integer ID to status label.
	 *	@param		int			$status		Status integer ID
	 *	@return		string					Status label
	 *	@throws		DomainException			if given status is not existing
	 */
	public static function getStatusLabel( int $status ): string
	{
		if( !in_array(	$status, self::STATUSES, TRUE ) )
			throw new DomainException( 'Invalid status' );
		return self::STATUS_MAP[$status];
	}

	/**
	 *	Resolves type label to type integer ID.
	 *	@param		string		$type		Type label
	 *	@return		int						Type integer ID
	 *	@throws		DomainException			if given type is not existing
	 */
	public static function getTypeId( string $type ): int
	{
		if( !in_array( $type, self::TYPE_LABELS, TRUE ) )
			throw new DomainException( 'Invalid type' );
		return array_flip( self::TYPE_LABELS )[$type];
	}

	/**
	 *	Resolves type integer ID to type label.
	 *	@param		int			$type		Type integer ID
	 *	@return		string					Type label
	 *	@throws		DomainException			if given type is not existing
	 */
	public static function getTypeLabel( int $type ): string
	{
		if( !in_array( $type, self::TYPES, TRUE ) )
			throw new DomainException( 'Invalid type' );
		return self::TYPE_MAP[$type];
	}
}
