<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_User extends Logic
{
	const EXTEND_NOTHING	= 0;
	const EXTEND_ROLE		= 1;
	const EXTEND_GROUPS		= 2;
	const EXTEND_RIGHTS		= 4;
	const EXTEND_AVATAR		= 8;
	const EXTEND_SETTINGS	= 16;

	protected Model_User $modelUser;
	protected Model_Group $modelGroup;
	protected Model_Group_User $modelGroupUser;
	protected Model_Role $modelRole;

	protected function __onInit(): void
	{
		$this->modelUser		= new Model_User( $this->env );
		$this->modelGroup		= new Model_Group( $this->env );
		$this->modelGroupUser	= new Model_Group_User( $this->env );
		$this->modelRole		= new Model_Role( $this->env );
	}

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
			if( $extend & self::EXTEND_ROLE )
				$user->role	= $this->modelRole->get( $userId );
			if( $extend & self::EXTEND_GROUPS )
				$user->groups	= $this->getUserGroups( $user );
		}
		return $user;
	}

	/**
	 * @param	Entity_User		$user
	 * @return	Entity_Group[]
	 */
	public function getUserGroups( Entity_User $user ): array
	{
		$groupIds	= $this->modelGroupUser->getByIndex( 'userId', $user->userId, [], ['groupId'] );
		return $this->modelGroup->getAllByIndex( 'groupId', $groupIds );
	}

	public function isUserInGroup( int|string|Entity_User $user, int|string|Entity_Group $group ): bool
	{
		$userId		= is_object( $user ) ? $user->userId : $user;
		$groupId	= is_object( $group ) ? $group->groupId : $group;
		return (bool) $this->modelGroupUser->countByIndices( ['userId' => $userId, 'groupId' => $groupId] );
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
}