<?php
class Logic_Authentication extends CMF_Hydrogen_Logic
{
	protected $env;
	protected $backend;
	protected $backends	= array();
	protected $session;

	const STATUS_UNKNOWN		= 0;
	const STATUS_IDENTIFIED		= 1;
	const STATUS_AUTHENTICATED	= 2;

	public function checkPassword( $userId, string $password )
	{
		return $this->backend->checkPassword( $userId, $password );
	}

	public function clearCurrentUser()
	{
		$this->backend->clearCurrentUser();
		return $this;
	}

	public function getBackends(): array
	{
		return $this->backends;
	}

	public function getCurrentRole( bool $strict = TRUE )
	{
		return $this->backend->getCurrentRole( $strict );
	}

	public function getCurrentRoleId( bool $strict = TRUE )
	{
		return $this->backend->getCurrentRoleId( $strict );
	}

	public function getCurrentUser( bool $strict = TRUE, bool $withRole = FALSE )
	{
		return $this->backend->getCurrentUser( $strict, $withRole );
	}

	public function getCurrentUserId( bool $strict = TRUE )
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
	 *	@param		integer		$userId			ID of user to get related users for
	 *	@param		boolean		$groupByModules	Flag: group related users by reporting modules
	 *	@return		array		Map of related users or list of reporting modules with related users
	 *	@triggers	Resource:User::getRelatedUsers
	 */
	public function getRelatedUsers( $userId, bool $groupByModules = FALSE ): array
	{
		$payload	= (object) array( 'userId' => $userId, 'list' => array() );
		$this->env->getCaptain()->callHook( 'Resource:Users', 'getRelatedUsers', $this, $payload );
		if( $groupByModules )
			return $payload->list;

		$list		= array();
		$map		= array();
		foreach( $payload->list as $group ){
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

	public function isIdentified(): bool
	{
		return $this->backend->isIdentified();
	}

	public function isCurrentUserId( $userId )
	{
		return $this->backend->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	Note this point of time as latest user activity if implemented by backend.
	 *	@access		protected
	 *	@return		void
	 */
	protected function noteUserActivity()
	{
		$this->backend->noteUserActivity();
		return $this;
	}

	public function registerBackend( string $key, string $path, string $label )
	{
		if( array_key_exists( $key, $this->backends ) )
			throw new RangeException( 'Backend "'.$key.'" is already registered' );
		$backend	= (object) array(
			'key'		=> $key,
			'path'		=> $path,
			'label'		=> $label,
			'module'	=> 'Resource_Authentication_Backend_'.$key,
			'classes'	=> (object) array(
				'logic'		=> NULL,
			),
		);
		$this->backends[$key]	= $backend;
		$classLogic		= 'Logic_Authentication_Backend_'.$key;
		if( !class_exists( $classLogic ) )
			throw new BadFunctionCallException( 'Authentication logic class for backend "'.$key.'" is not existing' );
		$backend->classes->logic = $classLogic;
		return $this;
	}

	public function setBackend( string $key )
	{
		if( !array_key_exists( $key, $this->backends ) )
			throw new OutOfRangeException( 'Authentication backend "'.$key.'" is not registered' );
		$backend		= $this->backends[$key];
		$factory		= new ReflectionMethod( $backend->classes->logic, 'getInstance' );
		$this->backend	= $factory->invokeArgs( NULL, array( $this->env ) );
//		$this->backend	= call_user_func_array( array( $className, 'getInstance' ), array( $this->env ) );
		$this->env->getMessenger()->noteNotice( 'Auth Backend: '.$key );
		return $this;
	}

	public function setAuthenticatedUser( $user )
	{
		$this->backend->setAuthenticatedUser( $user );
		return $this;
	}

	public function setIdentifiedUser( $user )
	{
		$this->backend->setIdentifiedUser( $user );
		return $this;
	}

	protected function __onInit()
	{
		$this->session		= $this->env->getSession();
		$this->env->getCaptain()->callHook( 'Auth', 'registerBackends', $this );
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
}
