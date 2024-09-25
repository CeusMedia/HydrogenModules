<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_User extends Logic
{
	protected Model_User $modelUser;
	protected Model_Group $modelGroup;
	protected Model_Group_User $modelGroupUser;

	protected function __onInit(): void
	{
		$this->modelUser		= new Model_User( $this->env );
		$this->modelGroup		= new Model_Group( $this->env );
		$this->modelGroupUser	= new Model_Group_User( $this->env );
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
	 * @param	int|string|Entity_User		$user
	 * @return	Entity_Group[]
	 */
	public function getUserGroups( int|string|Entity_User $user ): array
	{
		$userId		= is_object( $user ) ? $user->userId : $user;
		$groupIds	= $this->modelGroupUser->getByIndex( 'userId', $userId, [], ['groupId'] );
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