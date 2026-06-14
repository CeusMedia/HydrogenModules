<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;

/**
 * @todo check if deprecated, seems to be not used, right? if so, remove class
 */
class Logic_GroupRelation extends Logic
{
	protected Dictionary $moduleConfig;
	protected ?Model_Group_Relation $modelRelation	= NULL;
	protected bool $enabled							= FALSE;

	/**
	 *	@param		Entity_Group|int|string		$group
	 *	@param		ModuleDefinition|string		$module
	 *	@param		int|string					$entityId
	 *	@return		bool
	 */
	public function addModuleEntityRelation( Entity_Group|int|string $group, ModuleDefinition|string $module, int|string $entityId ): bool
	{
		if( !$this->enabled )
			return FALSE;
		$groupId	= is_object( $group ) ? $group->groupId : $group;
		$moduleId	= is_object( $module ) ? $module->id : $module;
		if( $this->isRelatedToModuleEntity( $groupId, $moduleId, $entityId ) )
			return FALSE;
		$this->modelRelation->add( [
			'groupId'	=> $groupId,
			'moduleId'	=> $moduleId,
			'entityId'	=> $entityId,
			'timestamp'	=> time(),
		] );
		return TRUE;
	}

	/**
	 *	@param		ModuleDefinition|string		$module
	 *	@return		array|int[]|string[]
	 *	@throws		ReflectionException
	 */
	public function getModuleEntityIdsFromCurrentGroups( ModuleDefinition|string $module ): array
	{
		if( !$this->enabled )
			return [];
		$groups	= Logic_Authentication::getInstance( $this->env )->getCurrentGroups();
		return $this->getModuleEntityIdsFromGroups( $module, $groups );
	}

	/**
	 *	@param		ModuleDefinition|string		$module
	 *	@param		array						$groups
	 *	@return		array<int|string>			List of entity IDs
	 */
	public function getModuleEntityIdsFromGroups( ModuleDefinition|string $module, array $groups ): array
	{
		if( !$this->enabled )
			return [];
		if( [] === $groups )
			return [];
		$groupIds	= [];
		foreach( $groups as $group )
			$groupIds[]	= is_object( $group ) ? $group->groupId : $group;
		$moduleId	= is_object( $module ) ? $module->id : $module;
		return $this->modelRelation->getAllByIndices( [
			'moduleId'	=> $moduleId,
			'groupId'	=> $groupIds,
		], [], [],  ['entityId'] );
	}

	/**
	 *	@param		ModuleDefinition|string		$module
	 *	@param		int|string					$entityId
	 *	@return		array<int|string,Entity_Group>
	 *	@throws		ReflectionException
	 */
	public function getGroups( ModuleDefinition|string $module, int|string $entityId ): array
	{
		if( !$this->enabled )
			return [];
		$moduleId	= is_object( $module ) ? $module->id : $module;

		$relationGroupIds	= $this->modelRelation->getAllByIndices( [
			'moduleId'	=> $moduleId,
			'entityId'	=> $entityId,
		], [], [], ['groupId'] );

		$list	= [];
		$modelGroup	= new Model_Group( $this->env );
		/** @var Entity_Group[] $groups */
		$groups		= $modelGroup->getAllByIndex( 'groupId', $relationGroupIds, ['title' => 'ASC'] );
		foreach( $groups as $group )
			$list[$group->groupId]	= $group;
		return $list;
	}

	/**
	 *	@param		Entity_Group|int|string		$group
	 *	@param		ModuleDefinition|string		$module
	 *	@param		int|string					$entityId
	 *	@return		bool
	 */
	public function isRelatedToModuleEntity( Entity_Group|int|string $group, ModuleDefinition|string $module, int|string $entityId ): bool
	{
		if( !$this->enabled )
			return FALSE;
		$groupId	= is_object( $group ) ? $group->groupId : $group;
		$moduleId	= is_object( $module ) ? $module->id : $module;
		return $this->modelRelation->hasByIndices( [
			'groupId'	=> $groupId,
			'moduleId'	=> $moduleId,
			'entityId'	=> $entityId,
		] );
	}

	/**
	 *	@param		Entity_Group|int|string		$group
	 *	@param		ModuleDefinition|string		$module
	 *	@param		int|string					$entityId
	 *	@return		bool
	 */
	public function removeModuleEntityRelation( Entity_Group|int|string $group, ModuleDefinition|string $module, int|string $entityId ): bool
	{
		if( !$this->enabled )
			return FALSE;
		$groupId	= is_object( $group ) ? $group->groupId : $group;
		$moduleId	= is_object( $module ) ? $module->id : $module;
		if( !$this->isRelatedToModuleEntity( $groupId, $moduleId, $entityId ) )
			return FALSE;
		$this->modelRelation->removeByIndices( [
			'groupId'	=> $groupId,
			'moduleId'	=> $moduleId,
			'entityId'	=> $entityId,
		] );
		return TRUE;
	}

	/**
	 *	@return		void
	 *	@throws		RuntimeException	if database connection check failed
	 *	@throws		ReflectionException	if cache setup fails to create cache backend by set cache adapter class
	 */
	protected function __onInit(): void
	{
		$this->moduleConfig		= $this->env->getModules()->get( 'Resource_Users' )->getConfigAsDictionary();
		$this->enabled			= $this->moduleConfig->get( 'group.useRelations' );
		if( $this->enabled )
			$this->modelRelation	= new Model_Group_Relation( $this->env );
	}
}
