<?php
use CeusMedia\HydrogenFramework\Logic;

class Logic_IP_Lock extends Logic
{
	protected Model_IP_Lock_Filter $modelFilter;
	protected Model_IP_Lock $modelLock;
	protected Model_IP_Lock_Reason $modelReason;

	/**
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function applyFilters(): bool
	{
		$ip		= getEnv( 'REMOTE_ADDR' );
		$uri	= getEnv( 'REQUEST_URI' );
		$method	= getEnv( 'REQUEST_METHOD' );
		if( $this->isLockedIp( $ip ) )
			return FALSE;
		$conditions	= ['status' => Model_IP_Lock_Filter::STATUS_ENABLED];
		$filters	= $this->modelFilter->getAll( $conditions );
		foreach( $filters as $filter ){
			/** @var ?Entity_Server_IP_Lock_Reason $reason */
			$reason		= $this->modelReason->get( $filter->reasonId );
			if( NULL === $reason || $reason->status < Model_IP_Lock_Reason::STATUS_ENABLED )
				continue;
			if( $filter->method && $filter->method != $method )
				continue;
			if( !preg_match( $filter->pattern, $uri ) )
				continue;
			$ipLockId	= $this->lockIp( $ip, $filter->reasonId, $filter );

//			if( $filter->lockStatus != Model_IP_Lock_Filter::STATUS_LOCKED )
//				$this->setStatus( $ipLockId, $filter->lockStatus );

			$this->modelFilter->edit( $filter->ipLockFilterId, ['appliedAt' => time()] );
			return TRUE;
		}
		return FALSE;
	}

	/**
	 *	@param		Entity_Server_IP_Lock	$lock
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function cancel( Entity_Server_IP_Lock $lock ): bool
	{
		if( $lock->status == Model_IP_Lock::STATUS_CANCELLED )
			return FALSE;																			//  indicate: lock already cancelled
		return $this->setStatus( $lock, Model_IP_Lock::STATUS_CANCELLED );					//  cancel lock and return TRUE
	}

	public function count( array $conditions ): int
	{
		return $this->modelLock->count( $conditions );
	}

	/**
	 *	@param		Entity_Server_IP_Lock	$lock
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function countView( Entity_Server_IP_Lock $lock ): void
	{
		$this->modelLock->edit( $lock->ipLockId, [
			'views'		=> $lock->views + 1,
			'visitedAt'	=> time(),
		] );
	}

	/**
	 *	@param		int|string		$ipLockId
	 *	@param		bool			$strict
	 *	@return		?Entity_Server_IP_Lock
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( int|string $ipLockId, bool $strict = TRUE ): ?Entity_Server_IP_Lock
	{
		/** @var ?Entity_Server_IP_Lock $lock */
		$lock	= $this->modelLock->get( $ipLockId );
		if( NULL === $lock ){
			if( $strict )
				throw new RangeException( 'Invalid lock ID' );
			return NULL;
		}
		$lock->reason	= $this->modelReason->get( $lock->reasonId );
		if( $lock->status >= Model_IP_Lock::STATUS_LOCKED && $lock->reason->duration ){
			$lock->unlockAt	= $lock->lockedAt + $lock->reason->duration;
			$lock->unlockIn	= $lock->unlockAt - time();
		}
		if( $lock->filterId ){
			$lock->filter = $this->modelFilter->get( $lock->filterId );
		}
		return $lock;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array<Entity_Server_IP_Lock>
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getAll( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$list	= $this->modelLock->getAll( $conditions, $orders, $limits );
		foreach( $list as $lock )
			$lock->reason	= $this->modelReason->get( $lock->reasonId );
		return $list;
	}

	/**
	 *	@param		string		$ip
	 *	@param		bool		$strict
	 *	@return		?Entity_Server_IP_Lock
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getByIp( string $ip, bool $strict = TRUE ): ?Entity_Server_IP_Lock
	{
		/** @var ?Entity_Server_IP_Lock $lock */
		$lock	= $this->modelLock->getByIndex( 'IP', $ip );
		if( NULL === $lock ){
			if( $strict )
				throw new RangeException( 'Invalid lock IP' );
			return NULL;
		}
		return $this->get( $lock->ipLockId );
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array<Entity_Server_IP_Lock_Filter>
	 */
	public function getFilters( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelFilter->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		int|string	$reasonId
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array<Entity_Server_IP_Lock_Filter>
	 */
	public function getFiltersOfReason( int|string $reasonId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions['reasonId']	= $reasonId;
		return $this->getFilters( $conditions, $orders, $limits );
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array<Entity_Server_IP_Lock_Reason>
	 */
	public function getReasons( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelReason->getAll( $conditions, $orders, $limits );
	}

	/**
	 *	@param		string		$ip
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function isLockedIp( string $ip ): bool
	{
		$lock	= $this->getByIp( $ip, FALSE );
		if( NULL === $lock )
			return FALSE;
		return $lock->status >= Model_IP_Lock::STATUS_LOCKED;										//  lock is set or has release request
	}

	/**
	 *	@param		Entity_Server_IP_Lock	$lock
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function lock( Entity_Server_IP_Lock $lock ): bool
	{
		$states	= [
			Model_IP_Lock::STATUS_UNLOCKED,
			Model_IP_Lock::STATUS_REQUEST_LOCK,
			Model_IP_Lock::STATUS_REQUEST_UNLOCK
		];
		if( !in_array( $lock->status, $states ) )													//  transition is not allowed
			return FALSE;																			//  indicate: lock exists but is not activatable
		return $this->setStatus( $lock, Model_IP_Lock::STATUS_LOCKED );						//  realize lock and return TRUE
	}

	/**
	 *	@param		string				$ip
	 *	@param		int|string|NULL		$reasonId
	 *	@param		object|NULL			$filter
	 *	@return		Entity_Server_IP_Lock
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function lockIp( string $ip, int|string|NULL $reasonId = NULL, ?object $filter = NULL ): Entity_Server_IP_Lock
	{
		$lock	= $this->getByIp( $ip, FALSE );
		if( NULL === $lock ){
			$lockId	= $this->modelLock->add( [
				'filterId'	=> $filter ? $filter->ipLockFilterId : 0,
				'reasonId'	=> (int) $reasonId ?: 0,
				'status'	=> Model_IP_Lock::STATUS_REQUEST_LOCK,
				'IP'		=> trim( $ip ),
				'uri'		=> getEnv( 'REQUEST_URI' ),
				'lockedAt'	=> time(),
			] );
			return $this->get( $lockId );
		}
		if( $reasonId )
			$this->modelLock->edit( $lock->ipLockId, ['reasonId' => $reasonId] );
		$this->lock( $lock );
		return $this->get( $lock->ipLockId );
	}

	/**
	 *	@param		Entity_Server_IP_Lock	$lock
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( Entity_Server_IP_Lock $lock ): bool
	{
		return $this->modelLock->remove( $lock->ipLockId );
	}

	public function removeAll( bool $locks = TRUE, bool $filters = FALSE, bool $reasons = FALSE ): void
	{
		if( $locks )
			$this->modelLock->truncate();
		if( $filters )
			$this->modelFilter->truncate();
		if( $reasons )
			$this->modelReason->truncate();
	}

	/**
	 *	@param		Entity_Server_IP_Lock	$lock
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function requestUnlock( Entity_Server_IP_Lock $lock ): bool
	{
		if( $lock->status !== Model_IP_Lock::STATUS_LOCKED )
			return FALSE;																			//  indicate: lock is not locked
		return $this->setStatus( $lock, Model_IP_Lock::STATUS_REQUEST_UNLOCK );				//  note unlock request and return TRUE
	}

	/**
	 *	@param		Entity_Server_IP_Lock	$lock
	 *	@param		int						$status
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setStatus( Entity_Server_IP_Lock $lock, int $status ): bool
	{
		$data	= ['status' => $status];
		if( $status == Model_IP_Lock::STATUS_UNLOCKED )
			$data['unlockedAt']	= time();
		else if( $status == Model_IP_Lock::STATUS_LOCKED ){
			$data['lockedAt']	= time();
			$this->modelReason->edit( $lock->reasonId, ['appliedAt' => time()] );			//  note reason apply time
		}
		return (bool) $this->modelLock->edit( $lock->ipLockId, $data );
	}

	/**
	 *	@param		Entity_Server_IP_Lock|string	$lockOrIp
	 *	@param		bool $strict
	 *	@return		bool|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function unlockIfOverdue( Entity_Server_IP_Lock|string $lockOrIp, bool $strict = TRUE ): ?bool
	{
		if( is_string( $lockOrIp ) ){
			$lockOrIp	= $this->getByIp( $lockOrIp, $strict );
			if( NULL === $lockOrIp )
				return NULL;
		}
		if( $lockOrIp->status < Model_IP_Lock::STATUS_LOCKED )										//  lock is neither locked nor has release request
			return NULL;																			//  indicate: lock not locked
		if( !$lockOrIp->unlockAt || $lockOrIp->unlockAt > time() )									//  unlock date is in the future
			return FALSE;																			//  indicate: lock is not overdue
		return (bool) $this->unlock( $lockOrIp );											//  release lock and return TRUE
	}

	/**
	 *	@param		Entity_Server_IP_Lock	$lock
	 *	@return		bool|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function unlock( Entity_Server_IP_Lock $lock ): bool|NULL
	{
		if( $lock->status !== Model_IP_Lock::STATUS_LOCKED )
			return FALSE;																			//  indicate: lock not locked
		return $this->setStatus( $lock, Model_IP_Lock::STATUS_UNLOCKED );						//  unlock lock and return TRUE
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->modelLock	= new Model_IP_Lock( $this->env );
		$this->modelFilter	= new Model_IP_Lock_Filter( $this->env );
		$this->modelReason	= new Model_IP_Lock_Reason( $this->env );
	}
}
