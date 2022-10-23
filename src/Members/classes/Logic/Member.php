<?php

use CeusMedia\HydrogenFramework\Environment;

class Logic_Member
{
	protected static $instance;

	public static function getInstance( Environment $env ): self
	{
		if( !self::$instance )
			self::$instance	= new Logic_Member( $env );
		return self::$instance;
	}

	public function getRelatedUserIds( $userId, $status = NULL )
	{
		$userIds	= [];
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

	public function getUserIdsByQuery( string $query ): array
	{
		$dbc		= $this->env->getDatabase();
		$prefix		= $dbc->getPrefix();
		$userIds	= [];

		$query		= str_replace( ' ', '%', trim( $query ) );
		$conditions	= array(
			'status'	=> '>= '.Model_User::STATUS_UNCONFIRMED,
			'username'	=> '%'.$query.'%'
		);
		$byUsername	= $this->modelUser->getAll( $conditions, ['username' => 'ASC'] );
		foreach( $byUsername as $user )
			$userIds[]	= $user->userId;

		$query		= vsprintf( "SELECT %s FROM %s HAVING %s", array(
			"userId, CONCAT(firstname, ' ', surname) AS fullname",
			$prefix.'users',
			"fullname LIKE '%".$query."%'",
		) );
		foreach( $dbc->query( $query )->fetchAll( PDO::FETCH_OBJ ) as $user )
			$userIds[]	= $user->userId;
		$userIds	= array_unique( $userIds );
		return $userIds;
	}

	public function getUserRelation( $currentUserId, $relatedUserId, $status = NULL )
	{
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

	public function getUsersWithRelations( $currentUserId, array $userIds, int $limit = 0, int $offset = 0 ): array
	{
		$key	= array_search( $currentUserId, $userIds );
		if ( $key !== FALSE )
			unset( $userIds[$key] );
		if( !$userIds )
			return [];
		$users		= $this->modelUser->getAllByIndex( 'userId', $userIds );
		if( $limit && count( $userIds ) > $limit )
			$users	= array_slice( $users, $offset, $limit );
		foreach( $users as $user )
			$user->relation	= $this->getUserRelation( $currentUserId, $user->userId );
		return $users;
	}

	protected function __clone()
	{
	}

	protected function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->messenger		= $this->env->getMessenger();
		$this->modelUser		= new Model_User( $this->env );
		$this->modelRelation	= new Model_User_Relation( $this->env );
		$this->userId			= $this->env->getSession()->get( 'auth_user_id' );
	}
}
