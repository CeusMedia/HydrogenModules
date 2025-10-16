<?php

interface Logic_Authentication_BackendInterface
{
	/**
	 *	@param		int|string		$userId
	 *	@param		string			$password
	 *	@return		bool
	 */
	public function checkPassword( int|string $userId, string $password ): bool;

	public function clearCurrentUser(): void;

	/**
	 *	@param		bool		$strict
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getCurrentRole( bool $strict = TRUE ): ?object;

	/**
	 * @param		bool	$strict
	 * @return		int|string|NULL
	 */
	public function getCurrentRoleId( bool $strict = TRUE ): int|string|NULL;

	/**
	 *	@param		bool		$strict
	 *	@param		int			$extensions		Flags: extend user entity, default: Logic_User::EXTEND_NOTHING
	 *	@return		object|NULL
	 */
	public function getCurrentUser( bool $strict = TRUE, int $extensions = Logic_User::EXTEND_NOTHING ): ?object;

	/**
	 *	@param		bool		$strict
	 *	@return		int|string|NULL
	 *	@throws		RuntimeException	if not user is authenticated in session
	 */
	public function getCurrentUserId( bool $strict = TRUE ): int|string|NULL;

	/**
	 *	Indicates whether given user ID is currently authenticated within in this session.
	 *	@return bool
	 */
	public function isAuthenticated(): bool;

	/**
	 *	Indicates whether a user is at least identified within this session.
	 *	@return		bool
	 */
	public function isIdentified(): bool;

	/**
	 *	Indicates whether given user ID is currently authenticated within in this session.
	 *	@param		int|string		$userId
	 *	@return		bool
	 */
	public function isCurrentUserId( int|string $userId): bool;

	/**
	 *	@return		self
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function noteUserActivity(): self;

	/**
	 *	@param		Entity_User		$user
	 *	@return		self
	 */
	public function setAuthenticatedUser( Entity_User $user ): self;

	/**
	 *	@param		Entity_User		$user
	 *	@return		self
	 */
	public function setIdentifiedUser( Entity_User $user ): self;
}
