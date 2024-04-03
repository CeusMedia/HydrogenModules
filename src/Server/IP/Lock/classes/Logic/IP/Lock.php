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
			$reason		= $this->modelReason->get( $filter->reasonId );
			if( !$reason || $reason->status < Model_IP_Lock_Reason::STATUS_ENABLED )
				continue;
			if( $filter->method && $filter->method != $method )
				continue;
			if( !preg_match( $filter->pattern, $uri ) )
				continue;
			$ipLockId	= $this->lockIp( $ip, $filter->reasonId, $filter );

//			if( $filter->lockStatus != Model_IP_Lock_Filter::STATUS_LOCKED )
//				$this->setStatus( $ipLockId, $filter->lockStatus );

			$this->modelFilter->edit( $filter->ipLockFilterId, array( 'appliedAt' => time() ) );
			return TRUE;
		}
		return FALSE;
	}

	/**
	 *	@param		int|string		$ipLockId
	 *	@param		bool			$strict
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function cancel( int|string $ipLockId, bool $strict = TRUE ): bool
	{
		$lock	= $this->get( $ipLockId, $strict );
		if( $lock->status == Model_IP_Lock::STATUS_CANCELLED )
			return FALSE;																			//  indicate: lock already cancelled
		return $this->setStatus( $ipLockId, Model_IP_Lock::STATUS_CANCELLED, $strict );		//  cancel lock and return TRUE
	}

	public function count( array $conditions ): int
	{
		return $this->modelLock->count( $conditions );
	}

	/**
	 *	@param		int|string		$ipLockId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function countView( int|string $ipLockId ): void
	{
		$lock	= $this->get( $ipLockId );
		$this->modelLock->edit( $ipLockId, [
			'views'		=> $lock->views + 1,
			'visitedAt'	=> time(),
		] );
	}

	/**
	 *	@param		int|string		$ipLockId
	 *	@param		bool		$strict
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( int|string $ipLockId, bool $strict = TRUE ): object|NULL
	{
		$lock	= $this->modelLock->get( $ipLockId );
		if( !$lock ){
			if( $strict )
				throw new RangeException( 'Invalid lock ID' );
			return NULL;
		}
		$lock->unlockAt	= 0;
		$lock->unlockIn	= 0;
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
	 *	@return		array
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
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getByIp( string $ip, bool $strict = TRUE ): object|NULL
	{
		$lock	= $this->modelLock->getByIndex( 'IP', $ip );
		if( !$lock ){
			if( $strict )
				throw new RangeException( 'Invalid lock IP' );
			return NULL;
		}
		return $this->get( $lock->ipLockId, $strict );
	}

	public function getFilters( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		return $this->modelFilter->getAll( $conditions, $orders, $limits );
	}

	public function getFiltersOfReason( string $reasonId, array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$conditions['reasonId']	= $reasonId;
		return $this->getFilters( $conditions, $orders, $limits );
	}

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
		if( !$lock )
			return FALSE;
		return $lock->status >= Model_IP_Lock::STATUS_LOCKED;										//  lock is set or has release request
	}

	/**
	 *	@param		int|string		$ipLockId
	 *	@param		bool			$strict
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function lock( int|string $ipLockId, bool $strict = TRUE ): bool
	{
		$lock	= $this->get( $ipLockId, $strict );
		$states	= [
			Model_IP_Lock::STATUS_UNLOCKED,
			Model_IP_Lock::STATUS_REQUEST_LOCK,
			Model_IP_Lock::STATUS_REQUEST_UNLOCK
		];
		if( !in_array( $lock->status, $states ) )													//  transition is not allowed
			return FALSE;																			//  indicate: lock exists but is not activatable
		return $this->setStatus( $ipLockId, Model_IP_Lock::STATUS_LOCKED, $strict );				//  realize lock and return TRUE
	}

	/**
	 *	@param		string			$ip
	 *	@param		int|string|NULL		$reasonId
	 *	@param		object|NULL		$filter
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function lockIp( string $ip, int|string|NULL $reasonId = NULL, ?object $filter = NULL ): string
	{
		$lock	= $this->getByIp( $ip, FALSE );
		if( !$lock ){
			$lockId	= $this->modelLock->add( [
				'filterId'	=> $filter ? $filter->ipLockFilterId : 0,
				'reasonId'	=> (int) $reasonId ?: 0,
				'status'	=> Model_IP_Lock::STATUS_REQUEST_LOCK,
				'IP'		=> trim( $ip ),
				'uri'		=> getEnv( 'REQUEST_URI' ),
				'lockedAt'	=> time(),
			] );
			$lock	= $this->modelLock->get( $lockId );
		}
		if( $lock ){
			if( $reasonId )
				$this->modelLock->edit( $lock->ipLockId, ['reasonId' => $reasonId] );
			$this->lock( $lock->ipLockId, $reasonId );
		}
		return $lock->ipLockId;
	}

	/**
	 *	@param		int|string		$ipLockId
	 *	@param		bool			$strict
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $ipLockId, bool $strict = TRUE ): bool
	{
		$lock	= $this->get( $ipLockId, $strict );
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
	 *	@param		int|string		$ipLockId
	 *	@param		bool			$strict
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function requestUnlock( int|string $ipLockId, bool $strict = TRUE ): bool
	{
		$lock	= $this->get( $ipLockId, $strict );
		if( $lock->status != Model_IP_Lock::STATUS_LOCKED )
			return FALSE;																			//  indicate: lock is not locked
		$targetStatus	= Model_IP_Lock::STATUS_REQUEST_UNLOCK;
		return $this->setStatus( $ipLockId, $targetStatus, $strict );								//  note unlock request and return TRUE
	}

	/**
	 *	@param		int|string		$ipLockId
	 *	@param		int				$status
	 *	@param		bool			$strict
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setStatus( int|string $ipLockId, int $status, bool $strict = TRUE ): bool
	{
		$lock	= $this->get( $ipLockId, $strict );
		$data	= ['status' => $status];
		if( $status == Model_IP_Lock::STATUS_UNLOCKED )
			$data['unlockedAt']	= time();
		else if( $status == Model_IP_Lock::STATUS_LOCKED ){
			$data['lockedAt']	= time();
			$this->modelReason->edit( $lock->reasonId, array( 'appliedAt' => time() ) );			//  note reason apply time
		}
		return (bool) $this->modelLock->edit( $ipLockId, $data );
	}

	/**
	 *	@param		string $ipLockIdOrIp
	 *	@param		bool $strict
	 *	@return		bool|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function unlockIfOverdue( $ipLockIdOrIp, bool $strict = TRUE ): ?bool
	{
		if( is_string( $ipLockIdOrIp ) )
			$lock	= $this->get( $ipLockIdOrIp, $strict );
		else
			$lock	= $this->getByIp( $ipLockIdOrIp, $strict );
		if( !$lock )
			return NULL;
		if( $lock->status < Model_IP_Lock::STATUS_LOCKED )											//  lock is neither locked nor has release request
			return NULL;																			//  indicate: lock not locked
		if( !$lock->unlockAt || $lock->unlockAt > time() )											//  unlock date is in the future
			return FALSE;																			//  indicate: lock is not overdue
		return (bool) $this->unlock( $lock->ipLockId );												//  release lock and return TRUE
	}

	/**
	 *	@param		int|string		$ipLockId
	 *	@param		bool			$strict
	 *	@return		bool|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function unlock( int|string $ipLockId, bool $strict = TRUE ): bool|NULL
	{
		$lock	= $this->get( $ipLockId, $strict );
		if( !$lock )
			return NULL;
		if( $lock->status != Model_IP_Lock::STATUS_LOCKED )
			return FALSE;																			//  indicate: lock not locked
		return $this->setStatus( $ipLockId, Model_IP_Lock::STATUS_UNLOCKED, $strict );				//  unlock lock and return TRUE
	}

	protected function __onInit(): void
	{
		$this->modelLock	= new Model_IP_Lock( $this->env );
		$this->modelFilter	= new Model_IP_Lock_Filter( $this->env );
		$this->modelReason	= new Model_IP_Lock_Reason( $this->env );
	}
}
