<?php

use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Entity;

/**
 * @todo check if deprecated, seems to be not used, right? if so, remove class
 */
class Logic_GroupRelation extends Logic
{
	protected Model_Group_Relation $modelRelation;

	public function addModuleEntityRelation( Entity_Group|int|string $group, ModuleDefinition|string $module, int|string $entityId )
	{
		$groupId	= is_object( $group ) ? $group->groupId : $group;
		$moduleId	= is_object( $module ) ? $module->id : $module;
		if( $this->isRelatedToModuleEntity( $groupId, $moduleId, $entityId ) )
			return FALSE;
		$relationId	= $this->modelRelation->add( [
			'groupId'	=> $groupId,
			'moduleId'	=> $moduleId,
			'entityId'	=> $entityId,
			'timestamp'	=> time(),
		] );
		return TRUE;
	}

	public function getModuleEntityIdsFromCurrentGroups( ModuleDefinition|string $module ): array
	{
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

	public function getGroups( ModuleDefinition|string $module, int|string $entityId ): array
	{
		$moduleId	= is_object( $module ) ? $module->id : $module;

		$relations	= $this->modelRelation->getAllByIndices( [
			'moduleId'	=> $moduleId,
			'entityId'	=> $entityId,
		] );

		$list	= [];
		$modelGroup	= new Model_Group( $this->env );
		foreach( $relations as $relation )
			$list[$relation->groupId]	= $modelGroup->get( $relation->groupId );
		return $list;
	}

	public function isRelatedToModuleEntity( Entity_Group|int|string $group, ModuleDefinition|string $module, int|string $entityId ): bool
	{
		$groupId	= is_object( $group ) ? $group->groupId : $group;
		$moduleId	= is_object( $module ) ? $module->id : $module;
		return $this->modelRelation->hasByIndices( [
			'groupId'	=> $groupId,
			'moduleId'	=> $moduleId,
			'entityId'	=> $entityId,
		] );
	}

	public function removeModuleEntityRelation( Entity_Group|int|string $group, ModuleDefinition|string $module, int|string $entityId )
	{
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

	protected function __onInit(): void
	{
		$this->modelRelation = new Model_Group_Relation( $this->env );
	}
}
