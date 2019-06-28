<?php
class Logic_IP_Lock extends CMF_Hydrogen_Logic{

	protected $modelFilter;
	protected $modelLock;
	protected $modelReason;

	protected function __onInit() {
		$this->modelLock	= new Model_IP_Lock( $this->env );
		$this->modelFilter	= new Model_IP_Lock_Filter( $this->env );
		$this->modelReason	= new Model_IP_Lock_Reason( $this->env );
	}

	public function applyFilters(){
		$ip		= getEnv( 'REMOTE_ADDR' );
		$uri	= getEnv( 'REQUEST_URI' );
		$method	= getEnv( 'REQUEST_METHOD' );
		if( $this->isLockedIp( $ip ) )
			return;
		$conditions	= array( 'status' => Model_IP_Lock_Filter::STATUS_ENABLED );
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
			if( $filter->lockStatus != Model_IP_Lock_Filter::STATUS_LOCKED ){
				$this->setStatus( $ipLockId, $filter->lockStatus );
			}
			$this->modelFilter->edit( $filter->ipLockFilterId, array( 'appliedAt' => time() ) );
			return TRUE;
		}
		return FALSE;
	}

	public function cancel( $ipLockId, $strict = TRUE ){
		$lock	= $this->get( $ipLockId, $strict );
		if( $lock->status == Model_IP_Lock_Filter::STATUS_CANCELLED )
			return FALSE;																			//  indicate: lock already cancelled
		return $this->setStatus( $ipLockId, Model_IP_Lock_Filter::STATUS_CANCELLED, $strict );		//  cancel lock and return TRUE
	}

	public function count( $conditions ){
		return $this->modelLock->count( $conditions );
	}

	public function countView( $ipLockId ){
		$lock	= $this->get( $ipLockId );
		$this->modelLock->edit( $ipLockId, array(
			'views'		=> $lock->views + 1,
			'visitedAt'	=> time(),
		) );
	}

	public function get( $ipLockId, $strict = TRUE ){
		$lock	= $this->modelLock->get( $ipLockId );
		if( !$lock ){
			if( $strict )
				throw new RangeException( 'Invalid lock ID' );
			return NULL;
		}
		$lock->unlockAt	= 0;
		$lock->unlockIn	= 0;
		$lock->reason	= $this->modelReason->get( $lock->reasonId );
		if( $lock->status >= Model_IP_Lock_Filter::STATUS_LOCKED && $lock->reason->duration ){
			$lock->unlockAt	= $lock->lockedAt + $lock->reason->duration;
			$lock->unlockIn	= $lock->unlockAt - time();
		}
		if( $lock->filterId ){
			$lock->filter = $this->modelFilter->get( $lock->filterId );
		}
		return $lock;
	}

	public function getAll( $conditions = array(), $orders = array(), $limits = array() ){
		$list	= $this->modelLock->getAll( $conditions, $orders, $limits );
		foreach( $list as $nr => $lock )
			$lock->reason	= $this->modelReason->get( $lock->reasonId );
		return $list;
	}

	public function getByIp( $ip, $strict = TRUE ){
		$lock	= $this->modelLock->getByIndex( 'IP', $ip );
		if( !$lock ){
			if( $strict )
				throw new RangeException( 'Invalid lock IP' );
			return NULL;
		}
		return $this->get( $lock->ipLockId, $strict );
	}

	public function getFilters( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelFilter->getAll( $conditions, $orders, $limits );
	}

	public function getFiltersOfReason( $reasonId, $conditions = array(), $orders = array(), $limits = array() ){
		$conditions['reasonId']	= $reasonId;
		return $this->getFilters( $conditions, $orders, $limits );
	}

	public function getReasons( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelReason->getAll( $conditions, $orders, $limits );
	}

	public function isLockedIp( $ip ){
		$lock	= $this->getByIp( $ip, FALSE );
		if( !$lock )
			return FALSE;
		return $lock->status >= Model_IP_Lock_Filter::STATUS_LOCKED;								//  lock is set or has release request
	}

	public function lock( $ipLockId, $strict = TRUE ){
		$lock	= $this->get( $ipLockId, $strict );
		$states	= array(
			Model_IP_Lock_Filter::STATUS_UNLOCKED,
			Model_IP_Lock_Filter::STATUS_REQUEST_LOCK,
			Model_IP_Lock_Filter::STATUS_REQUEST_UNLOCK
		);
		if( !in_array( $lock->status, $states ) )													//  transition is not allowed
			return FALSE;																			//  indicate: lock exists but is not activatable
		return $this->setStatus( $ipLockId, Model_IP_Lock_Filter::STATUS_LOCKED, $strict );			//  realize lock and return TRUE
	}

	public function lockIp( $ip, $reasonId = NULL, $filter = NULL ){
		$lock	= $this->getByIp( $ip, FALSE );
		if( !$lock ){
			$lockId	= $this->modelLock->add( array(
				'filterId'	=> $filter ? $filter->ipLockFilterId : 0,
				'reasonId'	=> (int) $reasonId ? (int) $reasonId : 0,
				'status'	=> Model_IP_Lock_Filter::STATUS_REQUEST_LOCK,
				'IP'		=> trim( $ip ),
				'uri'		=> getEnv( 'REQUEST_URI' ),
				'lockedAt'	=> time(),
			) );
			$lock	= $this->modelLock->get( $lockId );
		}
		if( $lock ){
			if( $reasonId )
				$this->modelLock->edit( $lock->ipLockId, array( 'reasonId' => $reasonId ) );
			$this->lock( $lock->ipLockId, $reasonId );
		}
		return $lock->ipLockId;
	}

	public function remove( $ipLockId, $strict = TRUE ){
		$lock	= $this->get( $ipLockId, $strict );
		return $this->modelLock->remove( $lock->ipLockId );
	}

	public function removeAll( $locks = TRUE, $filters = FALSE, $reasons = FALSE ){
		if( $locks )
			$this->modelLock->truncate();
		if( $filters )
			$this->modelFilter->truncate();
		if( $reasons )
			$this->modelReason->truncate();
	}

	public function requestUnlock( $ipLockId, $strict = TRUE ){
		$lock	= $this->get( $ipLockId, $strict );
		if( $lock->status != Model_IP_Lock_Filter::STATUS_LOCKED )
			return FALSE;																			//  indicate: lock is not locked
		$targetStatus	= Model_IP_Lock_Filter::STATUS_REQUEST_UNLOCK;
		return $this->setStatus( $ipLockId, $targetStatis, $strict );								//  note unlock request and return TRUE
	}

	public function setStatus( $ipLockId, $status, $strict = TRUE ){
		$lock	= $this->get( $ipLockId, $strict );
		$data	= array( 'status' => $status );
		if( $status == Model_IP_Lock_Filter::STATUS_UNLOCKED )
			$data['unlockedAt']	= time();
		else if( $status == Model_IP_Lock_Filter::STATUS_LOCKED ){
			$data['lockedAt']	= time();
			$this->modelReason->edit( $lock->reasonId, array( 'appliedAt' => time() ) );			//  note reason apply time
		}
		return (bool) $this->modelLock->edit( $ipLockId, $data );
	}

	public function unlockIfOverdue( $ipLockIdOrIp, $strict = TRUE ){
		if( is_int( $ipLockIdOrIp ) )
			$lock	= $this->get( $ipLockId, $strict );
		else
			$lock	= $this->getByIp( $ipLockIdOrIp, $strict );
		if( !$lock )
			return NULL;
		if( $lock->status < Model_IP_Lock_Filter::STATUS_LOCKED )									//  lock is neither locked nor has release request
			return NULL;																			//  indicate: lock not locked
		if( !$lock->unlockAt || $lock->unlockAt > time() )											//  unlock date is in the future
			return FALSE;																			//  indicate: lock is not overdue
		return (bool) $this->unlock( $lock->ipLockId );												//  release lock and return TRUE
	}

	public function unlock( $ipLockId, $strict = TRUE ){
		$lock	= $this->get( $ipLockId, $strict );
		if( !$lock )
			return NULL;
		if( $lock->status != Model_IP_Lock_Filter::STATUS_LOCKED )
			return FALSE;																			//  indicate: lock not locked
		return $this->setStatus( $ipLockId, Model_IP_Lock_Filter::STATUS_UNLOCKED, $strict );		//  unlock lock and return TRUE
	}
}
?>
