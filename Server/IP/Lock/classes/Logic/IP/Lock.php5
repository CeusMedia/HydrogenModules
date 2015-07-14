<?php
class Logic_IP_Lock{

	static protected $instance;
	protected $env;
	protected $modelFilter;
	protected $modelLock;
	protected $modelReason;

	const STATUS_UNLOCKED       = -2;
	const STATUS_CANCELLED      = -1;
	const STATUS_REQUEST_LOCK   = 0;
	const STATUS_LOCKED         = 1;
	const STATUS_REQUEST_UNLOCK = 2;


	protected function  __construct( CMF_Hydrogen_Environment_Abstract $env ) {
		$this->env	= $env;
		$this->modelLock	= new Model_IP_Lock( $env );
		$this->modelFilter	= new Model_IP_Lock_Filter( $env );
		$this->modelReason	= new Model_IP_Lock_Reason( $env );
	}

	public function applyFilters(){
		$ip		= getEnv( 'REMOTE_ADDR' );
		$uri	= getEnv( 'REQUEST_URI' );
		$method	= getEnv( 'REQUEST_METHOD' );
		if( !$this->isLockedIp( $ip ) ){
			$filters	= $this->modelFilter->getAll( array( 'status' => 1 ) );
			foreach( $filters as $filter ){
				if( !$filter->method || $filter->method == $method ){
					if( preg_match( $filter->pattern, $uri ) ){
						$ipLockId	= $this->lockIp( $ip, $filter->reasonId );
						if( $filter->lockStatus != self::STATUS_LOCKED ){
							$this->setStatus( $ipLockId, $filter->lockStatus );
						}
						$this->modelFilter->edit( $filter->ipLockFilterId, array( 'appliedAt' => time() ) );
					}
				}
			}
		}
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

	public function get( $ipLockId ){
		$lock	= $this->modelLock->get( $ipLockId );
		if( $lock ){
			$lock->unlockAt	= 0;
			$lock->unlockIn	= 0;
			$lock->reason = $this->modelReason->get( $lock->reasonId );
			if( $lock->status >= self::STATUS_LOCKED && $lock->reason->duration ){
				$lock->unlockAt	= $lock->lockedAt + $lock->reason->duration;
				$lock->unlockIn	= $lock->unlockAt - time();
			}
			return $lock;
		}
		return NULL;
	}

	public function getAll( $conditions = array(), $orders = array(), $limits = array() ){
		$list	= $this->modelLock->getAll( $conditions, $orders, $limits );
		foreach( $list as $nr => $lock )
			$lock->reason	= $this->modelReason->get( $lock->reasonId );
		return $list;

	}

	public function getByIp( $ip ){
		$lock	= $this->modelLock->getByIndex( 'IPv4', $ip );
		if( $lock )
			return $this->get( $lock->ipLockId );
		return NULL;
	}

	static public function getInstance( CMF_Hydrogen_Environment_Abstract $env ) {
		if( !self::$instance )
			self::$instance	= new Logic_IP_Lock( $env );
		return self::$instance;
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
		$lock	= $this->getByIp( $ip );
		if( $lock && $lock->status >= self::STATUS_LOCKED )										//  lock is set or has release request
			return TRUE;
		return FALSE;
	}

	public function lock( $ipLockId ){
		$lock	= $this->get( $ipLockId );
		$states	= array(
			self::STATUS_UNLOCKED,
			self::STATUS_REQUEST_LOCK,
			self::STATUS_REQUEST_UNLOCK
		);
		if( $lock ){
			if( !in_array( $lock->status, $states ) )										//  transition is not allowed
				return FALSE;																//  indicate: lock exists but is not activatable
			return $this->setStatus( $ipLockId, self::STATUS_LOCKED );						//  realize lock and return TRUE
		}
		return NULL;																		//  indicate: lock not found
	}

	public function lockIp( $ip, $reasonId = NULL ){
		$lock	= $this->getByIp( $ip );
		if( !$lock ){
			$lockId	= $this->modelLock->add( array(
				'reasonId'	=> (int) $reasonId ? (int) $reasonId : 0,
				'status'	=> self::STATUS_REQUEST_LOCK,
				'IPv4'		=> trim( $ip ),
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
		return$lock->ipLockId;
	}

	public function remove( $ipLockId ){
		$lock	= $this->get( $ipLockId );
		$states	= array( self::STATUS_CANCELLED );
		if( $lock ){
			if( !in_array( $lock->status, $states ) )										//  transition is allowed
				return $this->setStatus( $ipLockId, self::STATUS_CANCELLED );				//  cancel lock and return TRUE
			return FALSE;																	//  indicate: lock exists but is not active
		}
		return NULL;																		//  indicate: lock not found
	}

	public function requestUnlock( $ipLockId ){
		$lock	= $this->get( $ipLockId );
		if( $lock ){
			if( $lock->status == self::STATUS_LOCKED )
				return $this->setStatus( $ipLockId, self::STATUS_REQUEST_UNLOCK );			//  note unlock request and return TRUE
			return FALSE;																	//  indicate: lock exists but is not active
		}
		return NULL;																		//  indicate: lock not found
	}

	public function setStatus( $ipLockId, $status ){
		$data	= array( 'status' => $status );
		if( $status == self::STATUS_UNLOCKED )
			$data['unlockedAt']	= time();
		else if( $status == self::STATUS_LOCKED ){
			$data['lockedAt']	= time();
			$lock	= $this->get( $ipLockId );
			$this->modelReason->edit( $lock->reasonId, array( 'appliedAt' => time() ) );	//  note reason apply time
		}
		return (bool) $this->modelLock->edit( $ipLockId, $data );
	}

	public function unlockIfOverdue( $ipLockIdOrIp ){
		if( is_int( $ipLockIdOrIp ) )
			$lock	= $this->get( $env, $ipLockId );
		else
			$lock	= $this->getByIp( $ipLockIdOrIp );
		if( $lock && $lock->status >= self::STATUS_LOCKED )									//  lock is set or has release request
			if( $lock->unlockAt && $lock->unlockAt <= time() )								//  unlock timestamp is in past
				return (bool) $this->unlock( $lock->ipLockId );								//  release lock and return TRUE
			return FALSE;																	//  indicate: lock exists but is not overdue
		return NULL;																		//  indicate: lock not found
	}

	public function unlock( $ipLockId ){
		$lock	= $this->get( $ipLockId );
		$states	= array( self::STATUS_LOCKED );
		if( $lock ){
			if( in_array( $lock->status, $states ) )										//  transition is allowed
				return $this->setStatus( $ipLockId, self::STATUS_UNLOCKED );
			return FALSE;
		}
		return NULL;
	}
}
?>
