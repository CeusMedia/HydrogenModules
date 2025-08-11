<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;

class Entity_ModuleEntityRelation extends Entity
{
	public const TYPE_ENTITY	= 'entity';
	public const TYPE_RELATION	= 'relation';
	public const TYPES			= [
		self::TYPE_ENTITY,
		self::TYPE_RELATION,
	];

	public ModuleDefinition $module;
	public string $type			= self::TYPE_ENTITY;
	public string $label;
	/** @var array<Entity_ModuleEntityRelationItem> $items */
	public array $items			= [];
	public int $count			= 0;
	public ?string $controller	= NULL;
	public ?string $action		= NULL;

	protected static array $mandatoryFields	= ['label'];

	/**
	 *	Checks sanity of values.
	 *	Throw explanatory exceptions on failure.
	 *	@param		array		$data		Reference to data array to work on
	 *	@return		void
	 */
	protected static function checkValues( array $data ): void
	{
		if( !in_array( $data['type'] ?? '', self::TYPES, TRUE ) )
			throw CeusMedia\Common\Exception\NotSupported::create( 'Invalid relation type' )
				->setDescription( 'Must be one of ['.join( ', ', self::TYPES ).'].' );
	}

	/**
	 *	Applies preset values dynamically created on manual construction.
	 *	@param		array		$array		Data array to work on
	 *	@return		array
	 */
	protected static function presetDynamicValues( array $array ): array
	{
		$array['count']	= count( $array['items'] );
		return $array;
	}
}