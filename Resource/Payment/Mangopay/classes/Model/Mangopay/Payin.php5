<?php
class Model_Mangopay_Payin extends CMF_Hydrogen_Model {

	const STATUS_UNKNOWN		= 0;
	const STATUS_CREATED		= 1;
	const STATUS_FAILED			= 2;
	const STATUS_SUCCEEDED		= 3;

	const TYPE_UNKNOWN				= 0;
	const TYPE_CARD					= 1;
	const TYPE_PREAUTHORIZED		= 2;
	const TYPE_BANK_WIRE			= 3;
	const TYPE_DIRECT_DEBIT			= 4;
	const TYPE_DIRECT_DEBIT_DIRECT	= 5;
	const TYPE_PAYPAL				= 6;

	protected $name		= 'mangopay_payins';
	protected $columns	= array(
		"payinId",
		"status",
		"id",
		"type",
		"amount",
		"currency",
		"data",
		"createdAt",
		"modifiedAt"
	);
	protected $primaryKey	= 'payinId';
	protected $indices		= array(
		"status",
		"id",
		"type",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	static public function getStatusId( $status ){
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

	static public function getStatusLabel( $status ){
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

	static public function getTypeId( $type ){
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

	static public function getTypeLabel( $type ){
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
?>
