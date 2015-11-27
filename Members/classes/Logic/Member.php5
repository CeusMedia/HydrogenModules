<?php
class Logic_Member{

	static protected $instance;

	protected function __clone(){}

	protected function __construct( $env ){
		$this->env		= $env;
		$this->messenger		= $this->env->getMessenger();
		$this->modelUser		= new Model_User( $this->env );
		$this->modelRelation	= new Model_User_Relation( $this->env );
		$this->userId			= $this->env->getSession()->get( 'userId' );
	}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new Logic_Member( $env );
		return self::$instance;
	}

	public function getRelatedUserIds( $userId, $status = NULL ){
		$userIds	= array();
		$relations	= $this->modelRelation->getAllByIndices( array(
			'fromUserId'	=> $userId,
			'status'		=> $status ? $status : "!0",
		) );
		foreach( $relations as $relation )
			$userIds[]	= $relation->toUserId;
		$relations	= $this->modelRelation->getAllByIndices( array(
			'toUserId'		=> $userId,
			'status'		=> $status ? $status : "!0",
		) );
		foreach( $relations as $relation )
			$userIds[]	= $relation->fromUserId;
		return $userIds;
	}

	public function getUserIdsByQuery( $query ){
		$dbc		= $this->env->getDatabase();
		$prefix		= $dbc->getPrefix();
		$userIds	= array();

		$query		= str_replace( ' ', '%', trim( $query ) );
		$conditions	= array( 'status' => '>=0', 'username' => '%'.$query.'%' );
		$byUsername	= $this->modelUser->getAll( $conditions, array( 'username' => 'ASC' ) );
		foreach( $byUsername as $user )
			$userIds[]	= $user->userId;

		$query		= "SELECT userId, CONCAT(firstname, ' ', surname) AS fullname FROM ".$prefix."users HAVING fullname LIKE '%".$query."%'";
		foreach( $dbc->query( $query )->fetchAll( PDO::FETCH_OBJ ) as $user )
			$userIds[]	= $user->userId;
		$userIds	= array_unique( $userIds );
		return $userIds;
	}

	public function getUserRelation( $currentUserId, $relatedUserId, $status = NULL ){
		$conditions	= array(
			'fromUserId'	=> $currentUserId,
			'toUserId'		=> $relatedUserId,
		);
		if( !is_null( $status ) )
			$conditions['status']	= $status;
		$relation	= $this->modelRelation->getByIndices( $conditions );
		if( $relation ){
			$relation->direction	= 'out';
			return $relation;
		}
		$conditions	= array(
			'fromUserId'	=> $relatedUserId,
			'toUserId'		=> $currentUserId,
		);
		if( !is_null( $status ) )
			$conditions['status']	= $status;
		$relation	= $this->modelRelation->getByIndices( $conditions );
		if( $relation ){
			$relation->direction	= 'in';
			return $relation;
		}
		return NULL;
	}

	public function getUsersWithRelations( $currentUserId, $userIds, $limit = 0, $offset = 0 ){
		$key	= array_search( $currentUserId, $userIds );
		if ( $key !== FALSE )
			unset( $userIds[$key] );
		if( !$userIds )
			return array();
		$users		= $this->modelUser->getAllByIndex( 'userId', $userIds );
		if( $limit && count( $userIds ) > $limit )
			$users	= array_slice( $users, $offset, $limit );
		foreach( $users as $user )
			$user->relation	= $this->getUserRelation( $currentUserId, $user->userId );
		return $users;
	}
}
