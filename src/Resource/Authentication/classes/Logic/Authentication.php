<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Authentication extends Logic
{
	public const STATUS_UNKNOWN			= 0;
	public const STATUS_IDENTIFIED		= 1;
	public const STATUS_AUTHENTICATED	= 2;

	protected Dictionary $session;
	protected ?Logic_Authentication_BackendInterface $backend	= NULL;
	protected array $backends			= [];

	public function checkPassword( int|string $userId, string $password ): bool
	{
		return $this->backend->checkPassword( $userId, $password );
	}

	public function clearCurrentUser(): self
	{
		$this->backend->clearCurrentUser();
		return $this;
	}

	public function getBackends(): array
	{
		return $this->backends;
	}

	public function getCurrentRole( bool $strict = TRUE ): ?object
	{
		return $this->backend->getCurrentRole( $strict );
	}

	public function getCurrentRoleId( bool $strict = TRUE ): int|string|NULL
	{
		return $this->backend->getCurrentRoleId( $strict );
	}

	public function getCurrentUser( bool $strict = TRUE, bool $withRole = FALSE ): ?object
	{
		return $this->backend->getCurrentUser( $strict, $withRole );
	}

	/**
	 *	@param		bool		$strict
	 *	@return		int|string|NULL
	 */
	public function getCurrentUserId( bool $strict = TRUE ): int|string|NULL
	{
		return $this->backend->getCurrentUserId( $strict );
	}

	/**
	 *	Returns all users connected to a user by its ID.
	 *	Related users will be collected by calling hook Resource:Users::getRelatedUsers.
	 *	All listing modules will report a list of users related to given user in their ways.
	 *
	 *	This method will return a plain map of user IDs and theirs users, by default.
	 *	For advanced uses, a list of reporting modules and their collected user relations can be returned instead.
	 *
	 *	@access		public
	 *	@param		int|string		$userId			ID of user to get related users for
	 *	@param		boolean			$groupByModules	Flag: group related users by reporting modules
	 *	@return		array			Map of related users or list of reporting modules with related users
	 *	@triggers	Resource:User::getRelatedUsers
	 *	@throws		ReflectionException
	 */
	public function getRelatedUsers( int|string $userId, bool $groupByModules = FALSE ): array
	{
		$payload	= ['userId' => $userId, 'list' => []];
		$this->env->getCaptain()->callHook( 'Resource:Users', 'getRelatedUsers', $this, $payload );

		if( $groupByModules )
			return $payload['list'];

		$list		= [];
		$map		= [];
		foreach( $payload['list'] ?? [] as $group ){
			if( $group->count )
				foreach( $group->list as $user )
					$list[$user->username]	= $user;
		}
		ksort( $list, SORT_NATURAL | SORT_FLAG_CASE );
		foreach( $list as $user )
			$map[$user->userId]	= $user;
		return $map;
	}

	public function hasFullAccess(): bool
	{
		if( !$this->isAuthenticated() )
			return FALSE;
		return $this->env->getAcl()->hasFullAccess( $this->getCurrentRoleId() );
	}

	public function isAuthenticated(): bool
	{
		return $this->backend->isAuthenticated();
	}

	public function isCurrentUserId( int|string $userId ): bool
	{
		return $this->backend->getCurrentUserId( FALSE ) == $userId;
	}

	public function isIdentified(): bool
	{
		return $this->backend->isIdentified();
	}

	public function registerBackend( string $key, string $path, string $label ): self
	{
		if( array_key_exists( $key, $this->backends ) )
			throw new RangeException( 'Backend "'.$key.'" is already registered' );
		$backend	= (object) array(
			'key'		=> $key,
			'path'		=> $path,
			'label'		=> $label,
			'module'	=> 'Resource_Authentication_Backend_'.$key,
			'classes'	=> (object) [
				'logic'		=> NULL,
			],
		);
		$this->backends[$key]	= $backend;
		$classLogic		= 'Logic_Authentication_Backend_'.$key;
		if( !class_exists( $classLogic ) )
			throw new BadFunctionCallException( 'Authentication logic class for backend "'.$key.'" is not existing' );
		$backend->classes->logic = $classLogic;
		return $this;
	}

	public function setAuthenticatedUser( $user ): self
	{
		$this->backend->setAuthenticatedUser( $user );
		return $this;
	}

	/**
	 *	@param		string		$key
	 *	@return		self
	 *	@throws		ReflectionException
	 */
	public function setBackend( string $key ): self
	{
		if( !array_key_exists( $key, $this->backends ) )
			throw new OutOfRangeException( 'Authentication backend "'.$key.'" is not registered' );
		$backend		= $this->backends[$key];
		$factory		= new ReflectionMethod( $backend->classes->logic, 'getInstance' );
		$this->backend	= $factory->invokeArgs( NULL, [$this->env] );
//		$this->backend	= call_user_func_array( [$className, 'getInstance'], [$this->env] );
//		$this->env->getMessenger()->noteNotice( 'Auth Backend: '.$key );
		return $this;
	}

	public function setIdentifiedUser( $user ): self
	{
		$this->backend->setIdentifiedUser( $user );
		return $this;
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->session		= $this->env->getSession();
		$payload	= [];
		$this->env->getCaptain()->callHook( 'Auth', 'registerBackends', $this, $payload );
		if( !$this->backends )
			throw new RuntimeException( 'No authentication backend installed' );
		$backend = $this->session->get( 'auth_backend' );
		if( !$backend ){
			$backends	= array_keys( $this->getBackends() );
			$backend	= current( $backends );
		}
		$this->setBackend( $backend );
		$this->noteUserActivity();
	}

	/**
	 *	Note this point of time as latest user activity if implemented by backend.
	 *	@access		protected
	 *	@return		self
	 */
	protected function noteUserActivity(): self
	{
		$this->backend->noteUserActivity();
		return $this;
	}
}
