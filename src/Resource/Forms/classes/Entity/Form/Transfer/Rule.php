<?php

use CeusMedia\Common\Exception\Data\Missing as MissingException;

class Entity_Form_Transfer_Rule
{
	public int|string $formTransferRuleId;
	public int|string $formTransferTargetId;
	public int|string $formId;
	public string $title;
	public string $rules;
	public int $createdAt;
	public int $modifiedAt;

	protected static array $mandatoryFields = ['formTransferTargetId', 'formId', 'title', 'rules'];

	public static function fromArray( array $data ): Entity_Form_Transfer_Rule
	{
		self::checkMandatoryFields( $data );
		return self::createInstanceFromArray( $data, [
			'formTransferRuleId'	=> 0,
		] );
	}

	public function __construct()
	{
		$this->createdAt		= time();
		$this->modifiedAt		= 0;
	}

	protected static function checkMandatoryFields( array $data ): void
	{
		foreach( self::$mandatoryFields as $key )
			if( !array_key_exists( $key, $data ) )
				throw MissingException::create( 'Missing data for key "'.$key.'"' );
	}

	protected static function createInstanceFromArray( $data, $presetData = [] ): static
	{
		$data	= array_merge( $presetData, $data );
		$className	= static::class;
		$instance	= new $className();
		foreach( $data as $key => $value )
			if( property_exists( $instance, $key ) )
				$instance->{$key} = $value;
		return $instance;
	}
}