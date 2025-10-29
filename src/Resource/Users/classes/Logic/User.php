<?php

use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Logic;

class Logic_User extends Logic
{
	public const EXTEND_NOTHING			= 0;
	public const EXTEND_ROLE			= 1;
	public const EXTEND_GROUPS			= 2;
	public const EXTEND_RIGHTS			= 4;
	public const EXTEND_AVATAR			= 8;
	public const EXTEND_SETTINGS		= 16;
	public const EXTEND_GROUP_RELATIONS	= 32;

	public const EXTENDS				= [
		self::EXTEND_NOTHING,
		self::EXTEND_ROLE,
		self::EXTEND_GROUPS,
		self::EXTEND_RIGHTS,
		self::EXTEND_AVATAR,
		self::EXTEND_SETTINGS,
		self::EXTEND_GROUP_RELATIONS,
	];

	protected Logic_Role $logicRole;
	protected Model_User $modelUser;
	protected Model_Group $modelGroup;
	protected Model_Group_User $modelGroupUser;
	protected Model_Group_Relation $modelGroupRelation;

	/**
	 *	@param		Entity_User			$user
	 *	@param		Entity_Group		$group
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addUserToGroup( Entity_User $user, Entity_Group $group ): string
	{
		/** @var ?Entity_Group_User $relation */
		$relation	= $this->modelGroupUser->getByIndices( [
			'userId'	=> $user->userId,
			'groupId'	=> $group->groupId
		] );
		if( NULL !== $relation )
			return $relation->groupUserId;

		return $this->modelGroupUser->add( [
			'userId'	=> $user->userId,
			'groupId'	=> $group->groupId,
			'status'	=> Model_Group::STATUS_ENABLED,
			'timestamp'	=> time(),
		] );
	}

	/**
	 *	Checks user ID for existence and returns user entity with optional extensions.
	 *	Throws exception on invalid ID.
	 *	@param		int|string		$userId
	 *	@param		int				$extend
	 *	@param		bool			$strict		Flag: throw exception if not existing, default: yes
	 *	@return		Entity_User|NULL
	 *	@throws		DomainException
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@todo		implement other extend modes, like avatar and settings
	 */
	public function checkId( int|string $userId, int $extend = self::EXTEND_NOTHING, bool $strict = TRUE ): ?Entity_User
	{
		/** @var ?Entity_User $user */
		$user	= $this->modelUser->get( $userId );
		if( NULL === $user ){
			if( $strict )
				throw new DomainException( 'Invalid user ID' );
			return NULL;
		}
		if( self::EXTEND_NOTHING !== $extend ){
			if( $extend & self::EXTEND_ROLE && 0 !== (int) $user->roleId  )
				$user->role	= $this->logicRole->get( $user->roleId );
			if( $extend & self::EXTEND_GROUPS )
				$user->groups	= $this->getUserGroups( $user );
			if( $extend & self::EXTEND_GROUP_RELATIONS ){
				$groupIds	= [];
				foreach( $this->getUserGroups( $user ) as $group )
					$groupIds[]	= $group->groupId;
				$moduleRelation	= new Model_Group_Relation( $this->env );
				$user->groupRelations	= $moduleRelation->getAllByIndices( [
					'groupId'	=> $groupIds,
				] );
			}
		}
		return $user;
	}

	/**
	 * @param	Entity_Group|int|string		$groupEntityOrId
	 * @return	int
	 */
	public function countGroupUsers( Entity_Group|int|string $groupEntityOrId ): int
	{
		$groupId	= $groupEntityOrId instanceof Entity_Group ? $groupEntityOrId->groupId : $groupEntityOrId;
		return $this->modelGroupUser->countByIndex( 'groupId', $groupId );
	}

	/**
	 * @param	Entity_Role|int|string		$roleEntityOrId
	 * @return	int
	 */
	public function countRoleUsers( Entity_Role|int|string $roleEntityOrId ): int
	{
		$roleId	= $roleEntityOrId instanceof Entity_Role ? $roleEntityOrId->roleId : $roleEntityOrId;
		return $this->modelUser->countByIndex( 'roleId', $roleId );
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		Entity_Group[]
	 */
	public function getGroups( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelGroup->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		Entity_Group|int|string		$groupEntityOrId
	 *	@return		Entity_User[]
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getGroupUsers( Entity_Group|int|string $groupEntityOrId ): array
	{
		$groupId	= $groupEntityOrId instanceof Entity_Group ? $groupEntityOrId->groupId : $groupEntityOrId;
		$users		= [];
		/** @var Entity_Group_User $relation */
		foreach( $this->modelGroupUser->getAllByIndex( 'groupId', $groupId ) as $relation )
			$users[$relation->userId]	= $this->modelUser->get( $relation->userId );
		return $users;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		Entity_Role[]
	 */
	public function getRoles( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->logicRole->getAll( $conditions, $orders, $limits );
	}

	/**
	 * @param		Entity_Role|int|string		$roleEntityOrId
	 * @return		Entity_User[]
	 */
	public function getRoleUsers( Entity_Role|int|string $roleEntityOrId ): array
	{
		$roleId	= $roleEntityOrId instanceof Entity_Role ? $roleEntityOrId->roleId : $roleEntityOrId;
		return $this->modelUser->getAllByIndex( 'roleId', $roleId );
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		Entity_User|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getUser( int|string $userId ): ?Entity_User
	{
		/** @var ?Entity_User $user */
		$user	= $this->modelUser->get( $userId );
		return $user;
	}

	/**
	 * @param	int|string|Entity_User		$user
	 * @return	Entity_Group[]
	 */
	public function getUserGroups( int|string|Entity_User $user ): array
	{
		$userId		= is_object( $user ) ? $user->userId : $user;
		$groupIds	= $this->modelGroupUser->getAllByIndex( 'userId', $userId, [], [], ['groupId'] );
		if( [] === $groupIds )
			return [];
		return $this->modelGroup->getAllByIndex( 'groupId', $groupIds );
	}

	public function hasGroupAccessToModuleEntity( int|string|Entity_User $user, ModuleDefinition|string $module, int|string $entityId ): bool
	{
		$userId		= is_object( $user ) ? $user->userId : $user;
		$moduleId	= is_object( $module ) ? $module->id : $module;
		$groupIds	= $this->modelGroupUser->getAllByIndex( 'userId', $userId, [], [], ['groupId'] );

		return $this->modelGroupRelation->hasByIndices( [
			'groupId'	=> $groupIds,
			'moduleId'	=> $moduleId,
			'entityId'	=> $entityId,
		] );
	}

	/**
	 *	@param		int|string|Entity_User		$user
	 *	@param		int|string|Entity_Group		$group
	 *	@return		bool
	 */
	public function isUserInGroup( int|string|Entity_User $user, int|string|Entity_Group $group ): bool
	{
		$userId		= is_object( $user ) ? $user->userId : $user;
		$groupId	= is_object( $group ) ? $group->groupId : $group;
		return 0 !== $this->modelGroupUser->countByIndices( ['userId' => $userId, 'groupId' => $groupId] );
	}

	/**
	 *	@param		Entity_User		$user
	 *	@param		Entity_Group	$group
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeUserFromGroup( Entity_User $user, Entity_Group $group ): bool
	{
		return (bool) $this->modelGroupUser->removeByIndices( [
			'userId'	=> $user->userId,
			'groupId'	=> $group->groupId
		] );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->modelUser			= new Model_User( $this->env );
		$this->modelGroup			= new Model_Group( $this->env );
		$this->modelGroupUser		= new Model_Group_User( $this->env );
		$this->modelGroupRelation	= new Model_Group_Relation( $this->env );
		$this->logicRole			= new Logic_Role( $this->env );
	}
}
